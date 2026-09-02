<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Ambil data keuangan, rekap filter tanggal, dan chart pendapatan.
     */
    public function getFinanceOverview(?string $startDate = null, ?string $endDate = null): array
    {
        $transactions = collect();

        if (!empty($startDate) && !empty($endDate)) {
            $transactions = Transaction::with(['customer', 'product'])
                ->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ])
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        // Pendapatan per bulan
        $incomePerMonth = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as total_income')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        $incomeData = array_fill(0, 12, 0);
        foreach ($incomePerMonth as $income) {
            $idx = (int)$income->month - 1;
            if ($idx >= 0 && $idx < 12) {
                $incomeData[$idx] = (int)$income->total_income;
            }
        }

        // Produk terjual per bulan
        $productsSoldPerMonth = Transaction::join('products', 'transactions.products_id', '=', 'products.id')
            ->select(
                DB::raw('MONTH(transactions.created_at) as month'),
                'products.name as product_name',
                DB::raw('SUM(transactions.qty) as total_sold')
            )
            ->whereYear('transactions.created_at', Carbon::now()->year)
            ->groupBy('month', 'products.name')
            ->orderBy('month', 'ASC')
            ->get();

        $productsPerMonth = [];
        $productNames = [];

        foreach ($productsSoldPerMonth as $product) {
            $monthIndex = (int)$product->month - 1;
            $productsPerMonth[$product->product_name][$monthIndex] = (int)$product->total_sold;
            if (!in_array($product->product_name, $productNames)) {
                $productNames[] = $product->product_name;
            }
        }

        foreach ($productNames as $productName) {
            for ($i = 0; $i < 12; $i++) {
                if (!isset($productsPerMonth[$productName][$i])) {
                    $productsPerMonth[$productName][$i] = 0;
                }
            }
            ksort($productsPerMonth[$productName]);
        }

        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        return [
            'transactions'     => $transactions,
            'months'           => $months,
            'incomeData'       => $incomeData,
            'productsPerMonth' => $productsPerMonth,
            'productNames'     => $productNames,
        ];
    }
}
