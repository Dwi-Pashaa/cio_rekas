<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Usaha;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
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
     * Hitung pendapatan per bulan dalam satu tahun berjalan.
     */
    public function getMonthlyIncomeData($user = null): array
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;

        $incomePerMonth = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as total_income')
        )
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
     * Ambil pelanggan teratas berdasarkan pembelanjaan/qty.
     */
    public function getTopCustomers($user = null, int $limit = 5)
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;

        return Customer::leftJoin('transactions', 'transactions.customers_id', '=', 'customers.id')
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
     * Secara cerdas mendeteksi ketersediaan produk di cabang kasir yang melayani.
     */
    public function findCustomerBySerialNumber(string $code, $user = null)
    {
        $customer = Customer::with(['product', 'type', 'status'])->where('code', $code)->first();

        if (!$customer) {
            return null;
        }

        // Resolusi cabang kasir:
        // Jika kasir memiliki branch_id (misal kasir Cabang A melayani pelanggan dari Cabang B),
        // cari produk dengan nama yang sama di cabang kasir yang sedang bertugas.
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
     * Proses transaksi kasir (aman dengan DB::transaction dan pemotongan stok pada cabang kasir yang melayani).
     */
    public function processCheckout(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $customer = Customer::find($data['customers_id']);

            if (!$customer) {
                throw new Exception('Data pelanggan tidak valid.');
            }

            // Dapatkan referensi produk (dari request atau dari data pelanggan)
            $selectedProduct = Product::find($data['products_id'] ?? $customer->products_id);
            if (!$selectedProduct) {
                throw new Exception('Data produk tidak ditemukan.');
            }

            // LOGIKA MULTI-CABANG:
            // Saat kasir Cabang A melayani pembeli dari Cabang B, stok yang berkurang HARUS milik Cabang A!
            $productToDeduct = $selectedProduct;

            if ($user && $user->branch_id) {
                // Cari produk dengan nama yang sama di cabang kasir yang bertugas
                $branchProduct = Product::where('branch_id', $user->branch_id)
                    ->where('name', $selectedProduct->name)
                    ->first();

                if ($branchProduct) {
                    $productToDeduct = $branchProduct;
                } elseif ($selectedProduct->branch_id != $user->branch_id) {
                    $cashierBranchName = $user->branch->name ?? 'Cabang Anda';
                    throw new Exception("Produk '{$selectedProduct->name}' belum terdaftar di stok {$cashierBranchName}.");
                }
            }

            $qtyToDeduct = intval($data['qty'] ?? $customer->limit ?? 1);

            if ($productToDeduct->stock <= 0 || $productToDeduct->stock < $qtyToDeduct) {
                $branchName = $productToDeduct->branch->name ?? 'cabang ini';
                throw new Exception("Stok barang di {$branchName} tidak mencukupi (sisa {$productToDeduct->stock} unit).");
            }

            $totalClean = str_replace(['.', ','], '', $data['total']);
            $paymentClean = str_replace(['.', ','], '', $data['payment']);

            $payload = [
                'customers_id' => $customer->id,
                'products_id'  => $productToDeduct->id,
                'qty'          => $qtyToDeduct,
                'total'        => $totalClean,
                'payment'      => $paymentClean,
                'users_id'     => $user->id,
                'branch_id'    => $user->branch_id ?? $productToDeduct->branch_id,
            ];

            $transaction = Transaction::create($payload);

            // Pengurangan stok pada cabang kasir yang melayani
            $productToDeduct->decrement('stock', $qtyToDeduct);

            return $transaction;
        });
    }

    /**
     * Ambil detail data untuk cetak struk transaksi.
     */
    public function getReceiptData(int|string $id): array
    {
        $transaction = Transaction::with(['customer', 'customer.type', 'customer.status', 'product', 'casier'])->findOrFail($id);
        $usaha = Usaha::latest()->first();

        return [
            'transaction' => $transaction,
            'usaha'       => $usaha,
        ];
    }
}
