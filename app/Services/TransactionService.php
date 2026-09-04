<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Usaha;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    protected XenditService $xenditService;
    protected NotificationService $notificationService;

    public function __construct(XenditService $xenditService, NotificationService $notificationService)
    {
        $this->xenditService = $xenditService;
        $this->notificationService = $notificationService;
    }

    /**
     * Ambil data transaksi terpaginasi dengan filter pencarian dan cabang.
     */
    public function getTransactionsPaginated($user, ?string $search = null, int $perPage = 10)
    {
        $level = $user->getRoleNames()[0] ?? null;

        return Transaction::with(['customer', 'product.branch', 'casier', 'branch'])
            ->when(!in_array($level, ['Admin', 'Manager']), function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('status', 'like', "%$search%")
                            ->orWhere('telp', 'like', "%$search%");
                    })
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%");
                    });
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Hitung pendapatan per bulan dalam satu tahun tertentu (default tahun berjalan).
     */
    public function getMonthlyIncomeData($user = null, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;

        $incomePerMonth = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as total_income')
        )
            ->whereYear('created_at', $year)
            ->where('payment_status', 'paid')
            ->when($user && !in_array($level, ['Admin', 'Manager']), function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        $incomeData = array_fill(0, 12, 0);
        foreach ($incomePerMonth as $income) {
            $monthIndex = (int)$income->month - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $incomeData[$monthIndex] = (int)$income->total_income;
            }
        }

        return $incomeData;
    }

    /**
     * Ambil pelanggan teratas berdasarkan pembelanjaan/qty dalam tahun tertentu.
     */
    public function getTopCustomers($user = null, int $limit = 10, ?int $year = null)
    {
        $year = $year ?? (int) date('Y');
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;

        return Customer::join('transactions', function ($join) use ($year) {
                $join->on('transactions.customers_id', '=', 'customers.id')
                    ->whereYear('transactions.created_at', $year);
            })
            ->leftJoin('products', 'transactions.products_id', '=', 'products.id')
            ->select(
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.telp as customer_telp',
                DB::raw('COALESCE(SUM(transactions.qty), 0) as total_spent'),
                DB::raw('COALESCE(SUM(transactions.total), 0) as total_nominal')
            )
            ->when($user && !in_array($level, ['Admin', 'Manager']), function ($query) use ($user) {
                $query->where('products.branch_id', $user->branch_id);
            })
            ->groupBy('customers.id', 'customers.name', 'customers.telp')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    /**
     * Cari pelanggan berdasarkan kode serial number.
     */
    public function findCustomerBySerialNumber(string $code, $user = null)
    {
        $customer = Customer::with(['product', 'type', 'status'])->where('code', $code)->first();

        if (!$customer) {
            return null;
        }

        if ($user && $user->branch_id && $customer->product) {
            $branchProduct = Product::where('branch_id', $user->branch_id)
                ->where('name', $customer->product->name)
                ->first();

            if ($branchProduct) {
                $customer->products_id = $branchProduct->id;
                $customer->setRelation('product', $branchProduct);
            }
        }

        $customer->amount = $customer->product->selling_price ?? 0;
        $customer->total = floatval($customer->product->selling_price ?? 0) * intval($customer->limit ?? 1);
        $customer->type_name = optional($customer->type)->name ?? 'Reguler';
        $customer->status_name = optional($customer->status)->name ?? 'Aktif';

        return $customer;
    }

    /**
     * Proses transaksi kasir / pesanan mandiri agent.
     */
    public function processCheckout(array $data, $user)
    {
        $usaha = Usaha::latest()->first();

        $transaction = DB::transaction(function () use ($data, $user, $usaha) {
            $customer = Customer::find($data['customers_id']);

            if (!$customer) {
                throw new Exception('Data pelanggan/agent tidak valid.');
            }

            $selectedProduct = Product::find($data['products_id'] ?? $customer->products_id);
            if (!$selectedProduct) {
                throw new Exception('Data produk tidak ditemukan.');
            }

            // 1. Tentukan Cabang Target Pemenuhan Stok
            $targetBranchId = $data['branch_id'] ?? ($user->branch_id ?? $selectedProduct->branch_id);
            $productToDeduct = $selectedProduct;

            if ($targetBranchId) {
                $branchProduct = Product::where('branch_id', $targetBranchId)
                    ->where('name', $selectedProduct->name)
                    ->first();

                if ($branchProduct) {
                    $productToDeduct = $branchProduct;
                } elseif ($selectedProduct->branch_id != $targetBranchId) {
                    $branch = Branch::find($targetBranchId);
                    $targetName = $branch ? $branch->name : 'Cabang yang dipilih';
                    throw new Exception("Produk '{$selectedProduct->name}' belum terdaftar di stok {$targetName}.");
                }
            }

            $qtyToDeduct = intval($data['qty'] ?? $customer->limit ?? 1);

            if ($productToDeduct->stock <= 0 || $productToDeduct->stock < $qtyToDeduct) {
                $branchName = optional($productToDeduct->branch)->name ?? 'cabang ini';
                throw new Exception("Stok barang di {$branchName} tidak mencukupi (sisa {$productToDeduct->stock} unit, dibutuhkan {$qtyToDeduct} unit).");
            }

            // 2. Opsi Penyerahan & Biaya Jasa Antar Kurir
            $deliveryType = $data['delivery_type'] ?? 'pickup';
            $deliveryFee = 0;
            if ($deliveryType === 'delivery') {
                $deliveryFee = (float) ($data['delivery_fee'] ?? ($usaha->delivery_fee ?? 0));
            }

            // 3. Tipe Pembayaran & Status Awal
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $paymentStatus = in_array($paymentMethod, ['xendit', 'transfer']) ? 'pending' : 'paid';

            $itemSubtotal = floatval($productToDeduct->selling_price ?? 0) * $qtyToDeduct;
            $grandTotal = $itemSubtotal + $deliveryFee;
            $paymentAmount = ($paymentMethod === 'xendit' || $paymentMethod === 'transfer') ? 0 : floatval(str_replace(['.', ','], '', $data['payment'] ?? $grandTotal));

            $payload = [
                'customers_id'   => $customer->id,
                'products_id'    => $productToDeduct->id,
                'qty'            => $qtyToDeduct,
                'delivery_type'  => $deliveryType,
                'delivery_fee'   => $deliveryFee,
                'total'          => $grandTotal,
                'payment'        => $paymentAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'users_id'       => $user->id,
                'branch_id'      => $targetBranchId,
            ];

            $trx = Transaction::create($payload);

            // Pengurangan stok pada cabang penyedia
            $productToDeduct->decrement('stock', $qtyToDeduct);

            return $trx;
        });

        // 4. Jika menggunakan Payment Gateway Xendit (Transfer Online)
        $invoiceData = null;
        if (in_array($transaction->payment_method, ['xendit', 'transfer'])) {
            try {
                $customer = Customer::find($transaction->customers_id);
                $invoiceData = $this->xenditService->createInvoice($transaction, $customer, $transaction->total, $usaha);
            } catch (Exception $e) {
                Log::error('[TransactionService] Gagal generate invoice Xendit: ' . $e->getMessage());
                throw new Exception('Gagal membuat invoice Xendit: ' . $e->getMessage());
            }
        }

        // 5. Trigger Notifikasi WhatsApp & Email:
        // - Untuk transaksi CASH (Tunai): Kirim notifikasi sekarang.
        // - Untuk transaksi TRANSFER (Xendit): Notifikasi WhatsApp HANYA dikirim setelah pembayaran BERHASIL via callback Xendit.
        if (!in_array($transaction->payment_method, ['xendit', 'transfer'])) {
            try {
                $this->notificationService->sendTransactionNotifications($transaction);
            } catch (Exception $e) {
                Log::error('[TransactionService] Trigger notifikasi transaksi error: ' . $e->getMessage());
            }
        }

        return [
            'transaction'  => $transaction,
            'invoice_url'  => $invoiceData['invoice_url'] ?? null,
            'invoice_id'   => $invoiceData['invoice_id'] ?? null,
        ];
    }

    /**
     * Ambil detail data untuk cetak struk transaksi.
     */
    public function getReceiptData(int|string $id): array
    {
        $transaction = Transaction::with(['customer', 'customer.type', 'customer.status', 'product', 'casier', 'branch'])->findOrFail($id);
        $usaha = Usaha::latest()->first();

        return [
            'transaction' => $transaction,
            'usaha'       => $usaha,
        ];
    }
}
