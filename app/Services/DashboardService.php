<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Ambil metrik KPI utama untuk dashboard (Hari ini, Bulan ini, Stok, Total).
     */
    public function getKpiMetrics($user): array
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $isRestricted = $user && !in_array($level, ['Admin', 'Manager']);

        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Penjualan Hari Ini
        $todayQuery = Transaction::whereDate('created_at', $today);
        if ($isRestricted) {
            $todayQuery->where('branch_id', $user->branch_id);
        }
        $todayIncome = (int)$todayQuery->sum('total');
        $todayTransactionsCount = $todayQuery->count();

        // Penjualan Bulan Ini
        $monthQuery = Transaction::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear);
        if ($isRestricted) {
            $monthQuery->where('branch_id', $user->branch_id);
        }
        $monthIncome = (int)$monthQuery->sum('total');
        $monthTransactionsCount = $monthQuery->count();

        // Total Transaksi Berhasil Sepanjang Waktu
        $totalQuery = Transaction::query();
        if ($isRestricted) {
            $totalQuery->where('branch_id', $user->branch_id);
        }
        $totalTransactionsCount = $totalQuery->count();

        // Total Sisa Stok Barang di Cabang
        $stockQuery = Product::query();
        if ($isRestricted && $user->branch_id) {
            $stockQuery->where('branch_id', $user->branch_id);
        }
        $totalStock = (int)$stockQuery->sum('stock');
        $lowStockCount = (clone $stockQuery)->where('stock', '<=', 5)->count();

        return [
            'today_income'              => $todayIncome,
            'today_transactions_count'  => $todayTransactionsCount,
            'month_income'              => $monthIncome,
            'month_transactions_count'  => $monthTransactionsCount,
            'total_transactions_count'  => $totalTransactionsCount,
            'total_stock'               => $totalStock,
            'low_stock_count'           => $lowStockCount,
        ];
    }

    /**
     * Ambil 5 transaksi terbaru untuk live activity feed.
     */
    public function getRecentTransactions($user, int $limit = 5)
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $isRestricted = $user && !in_array($level, ['Admin', 'Manager']);

        return Transaction::with(['customer', 'product', 'casier'])
            ->when($isRestricted, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Tren data penjualan bulanan untuk ApexCharts.
     */
    public function getMonthlySalesTrend($user): array
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $isRestricted = $user && !in_array($level, ['Admin', 'Manager']);

        $records = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as total')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->when($isRestricted, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        $monthlyData = array_fill(0, 12, 0);
        foreach ($records as $r) {
            $idx = (int)$r->month - 1;
            if ($idx >= 0 && $idx < 12) {
                $monthlyData[$idx] = (int)$r->total;
            }
        }

        return $monthlyData;
    }

    /**
     * Produk paling banyak terjual untuk diagram distribusi.
     */
    public function getTopSellingProducts($user, int $limit = 5)
    {
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $isRestricted = $user && !in_array($level, ['Admin', 'Manager']);

        return Product::join('transactions', 'transactions.products_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('SUM(transactions.qty) as total_sold'),
                DB::raw('SUM(transactions.total) as total_revenue')
            )
            ->when($isRestricted, function ($q) use ($user) {
                $q->where('products.branch_id', $user->branch_id);
            })
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }
}
