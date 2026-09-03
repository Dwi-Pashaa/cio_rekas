<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\DistributionHistory;
use App\Models\DistributionStock;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DistributionService
{
    /**
     * Dapatkan daftar seluruh produk beserta cabang dan stoknya.
     */
    public function getAllProductsWithBranch()
    {
        return Product::with(['branch', 'categori'])->orderBy('name')->orderBy('branch_id')->get();
    }

    /**
     * Dapatkan daftar nama produk voucher yang tersedia dalam sistem.
     */
    public function getAvailableProductNames()
    {
        $fromProducts = Product::distinct()->pluck('name')->toArray();
        $fromStocks = DistributionStock::distinct()->pluck('product_name')->toArray();
        $all = array_values(array_unique(array_filter(array_merge($fromProducts, $fromStocks))));
        sort($all);
        return $all;
    }

    /**
     * Dapatkan daftar user yang memiliki hak akses 'distribusi cabang'.
     */
    public function getEligibleCabangUsers(?int $excludeUserId = null)
    {
        $query = User::with('branch')->permission('distribusi cabang');

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Dapatkan daftar stok Distribusi Utama saat ini.
     */
    public function getUtamaStocks()
    {
        return DistributionStock::where('type', 'utama')->orderBy('product_name')->get();
    }

    /**
     * Dapatkan daftar stok Distribusi Cabang (per user atau semua untuk admin).
     */
    public function getCabangStocks(?User $user = null)
    {
        $query = DistributionStock::with(['user.branch'])->where('type', 'cabang');

        if ($user && !$user->hasRole('Admin') && !$user->can('distribusi utama')) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('product_name')->get();
    }

    /**
     * Alokasi / Tambah Stok ke Distribusi Utama oleh Admin.
     */
    public function addStockToUtama(string $productName, int $qty, ?string $notes, User $adminUser): DistributionHistory
    {
        if ($qty <= 0) {
            throw new Exception('Jumlah voucher harus lebih dari 0.');
        }

        return DB::transaction(function () use ($productName, $qty, $notes, $adminUser) {
            $stockRecord = DistributionStock::where('type', 'utama')
                ->where('product_name', $productName)
                ->lockForUpdate()
                ->first();

            if (!$stockRecord) {
                $stockRecord = DistributionStock::create([
                    'type' => 'utama',
                    'user_id' => null,
                    'product_name' => $productName,
                    'stock' => 0,
                ]);
            }

            $before = $stockRecord->stock;
            $after = $before + $qty;
            $stockRecord->update(['stock' => $after]);

            $refNo = 'DST-UTM-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            return DistributionHistory::create([
                'reference_no' => $refNo,
                'type' => 'admin_to_utama',
                'sender_id' => $adminUser->id,
                'receiver_id' => null,
                'target_branch_id' => null,
                'product_name' => $productName,
                'qty' => $qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $notes ?? 'Alokasi stok awal dari Admin ke Distribusi Utama',
            ]);
        });
    }

    /**
     * Distribusi Stok dari Distribusi Utama ke User Distribusi Cabang.
     */
    public function distributeUtamaToCabang(int $receiverUserId, string $productName, int $qty, ?string $notes, User $senderUser): DistributionHistory
    {
        if ($qty <= 0) {
            throw new Exception('Jumlah voucher harus lebih dari 0.');
        }

        $receiver = User::find($receiverUserId);
        if (!$receiver) {
            throw new Exception('User Distribusi Cabang penerima tidak ditemukan.');
        }

        return DB::transaction(function () use ($receiver, $productName, $qty, $notes, $senderUser) {
            // 1. Kunci & cek saldo stok Distribusi Utama
            $utamaStock = DistributionStock::where('type', 'utama')
                ->where('product_name', $productName)
                ->lockForUpdate()
                ->first();

            if (!$utamaStock || $utamaStock->stock < $qty) {
                $currentStock = $utamaStock ? $utamaStock->stock : 0;
                throw new Exception("Stok voucher Distribusi Utama untuk '{$productName}' tidak mencukupi (sisa: {$currentStock}, dibutuhkan: {$qty}).");
            }

            $beforeUtama = $utamaStock->stock;
            $afterUtama = $beforeUtama - $qty;
            $utamaStock->update(['stock' => $afterUtama]);

            // 2. Kunci & tambahkan saldo stok User Distribusi Cabang
            $cabangStock = DistributionStock::where('type', 'cabang')
                ->where('user_id', $receiver->id)
                ->where('product_name', $productName)
                ->lockForUpdate()
                ->first();

            if (!$cabangStock) {
                $cabangStock = DistributionStock::create([
                    'type' => 'cabang',
                    'user_id' => $receiver->id,
                    'product_name' => $productName,
                    'stock' => 0,
                ]);
            }

            $cabangStock->increment('stock', $qty);

            $refNo = 'DST-CAB-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            return DistributionHistory::create([
                'reference_no' => $refNo,
                'type' => 'utama_to_cabang',
                'sender_id' => $senderUser->id,
                'receiver_id' => $receiver->id,
                'target_branch_id' => null,
                'product_name' => $productName,
                'qty' => $qty,
                'stock_before' => $beforeUtama,
                'stock_after' => $afterUtama,
                'notes' => $notes ?? "Distribusi stok dari Utama ke {$receiver->name}",
            ]);
        });
    }

    /**
     * Distribusi Stok dari User Distribusi Cabang ke Cabang Fisik (Outlet/POS).
     */
    public function distributeCabangToBranch(int $targetBranchId, string $productName, int $qty, ?string $notes, User $senderUser): DistributionHistory
    {
        if ($qty <= 0) {
            throw new Exception('Jumlah voucher harus lebih dari 0.');
        }

        $branch = Branch::find($targetBranchId);
        if (!$branch) {
            throw new Exception('Cabang tujuan tidak ditemukan.');
        }

        return DB::transaction(function () use ($branch, $productName, $qty, $notes, $senderUser) {
            // 1. Kunci & cek saldo stok Distribusi Cabang milik sender
            // Jika sender adalah Admin dan tidak memiliki record cabang, izinkan kirim jika Utama ada stok atau ambil dari cabang record
            $cabangStockQuery = DistributionStock::where('type', 'cabang')
                ->where('product_name', $productName)
                ->where('user_id', $senderUser->id)
                ->lockForUpdate();

            $cabangStock = $cabangStockQuery->first();

            if (!$cabangStock || $cabangStock->stock < $qty) {
                $current = $cabangStock ? $cabangStock->stock : 0;
                throw new Exception("Stok voucher Distribusi Cabang Anda untuk '{$productName}' tidak mencukupi (sisa: {$current}, dibutuhkan: {$qty}).");
            }

            $beforeCabang = $cabangStock->stock;
            $afterCabang = $beforeCabang - $qty;
            $cabangStock->update(['stock' => $afterCabang]);

            // 2. Update atau Buat produk di cabang toko tujuan
            $targetProduct = Product::where('branch_id', $branch->id)
                ->where('name', $productName)
                ->first();

            if ($targetProduct) {
                $targetProduct->increment('stock', $qty);
            } else {
                // Clone atribut dari produk yang sudah ada dengan nama yang sama
                $template = Product::where('name', $productName)->first();

                Product::create([
                    'name' => $productName,
                    'code' => rand(10000, 99999),
                    'categories_id' => $template ? $template->categories_id : 1,
                    'stock' => $qty,
                    'base_price' => $template ? $template->base_price : 0,
                    'selling_price' => $template ? $template->selling_price : 0,
                    'branch_id' => $branch->id,
                ]);
            }

            $refNo = 'DST-OUT-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            return DistributionHistory::create([
                'reference_no' => $refNo,
                'type' => 'cabang_to_branch',
                'sender_id' => $senderUser->id,
                'receiver_id' => null,
                'target_branch_id' => $branch->id,
                'product_name' => $productName,
                'qty' => $qty,
                'stock_before' => $beforeCabang,
                'stock_after' => $afterCabang,
                'notes' => $notes ?? "Distribusi stok dari Cabang Distribusi ke Outlet {$branch->name}",
            ]);
        });
    }

    /**
     * Ambil riwayat distribusi terpaginasi dengan filter.
     */
    public function getHistoriesPaginated(?string $type = null, ?User $user = null, ?string $search = null, int $perPage = 10)
    {
        $query = DistributionHistory::with(['sender', 'receiver', 'targetBranch']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($user && !$user->hasRole('Admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('sender', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiver', function ($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('targetBranch', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('id', 'DESC')->paginate($perPage);
    }
}
