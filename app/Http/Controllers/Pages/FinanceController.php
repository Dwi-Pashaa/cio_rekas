<?php

namespace App\Http\Controllers\Pages;

use App\Exports\Transaction\ListTransactionExportFilter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $selectedYear = (int) ($request->year ?? date('Y'));

        // Default rentang tanggal: Awal bulan ini s/d hari ini
        $start_date = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $end_date   = $request->end_date ?? Carbon::now()->toDateString();

        // Transaksi dalam rentang tanggal terfilter
        $transaction = Transaction::with(['customer', 'customer.type', 'customer.status', 'product', 'casier', 'branch'])
            ->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ])
            ->when($user && !in_array($level, ['Admin', 'Manager']) && $user->branch_id, function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->orderBy('id', 'DESC')
            ->get();

        // Daftar tahun yang tersedia dari database
        $availableYears = Transaction::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->pluck('year')
            ->toArray();

        if (!in_array((int) date('Y'), $availableYears)) {
            array_unshift($availableYears, (int) date('Y'));
        }

        // 1. Pendapatan bulanan sesuai tahun yang dipilih
        $incomePerMonth = Transaction::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total_income')
            )
            ->whereYear('created_at', $selectedYear)
            ->when($user && !in_array($level, ['Admin', 'Manager']) && $user->branch_id, function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        // 2. Barang terjual per bulan sesuai tahun yang dipilih
        $productsSoldPerMonth = Transaction::join('products', 'transactions.products_id', '=', 'products.id')
            ->select(
                DB::raw('MONTH(transactions.created_at) as month'),
                'products.name as product_name',
                DB::raw('SUM(transactions.qty) as total_sold')
            )
            ->whereYear('transactions.created_at', $selectedYear)
            ->when($user && !in_array($level, ['Admin', 'Manager']) && $user->branch_id, function ($query) use ($user) {
                $query->where('products.branch_id', $user->branch_id);
            })
            ->groupBy('month', 'products.name')
            ->orderBy('month', 'ASC')
            ->get();

        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        $incomeData = array_fill(0, 12, 0);
        foreach ($incomePerMonth as $income) {
            $monthIndex = (int)$income->month - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $incomeData[$monthIndex] = (int)$income->total_income;
            }
        }

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

        return view("pages.finance.index", compact(
            "transaction",
            "months",
            "incomeData",
            "productsPerMonth",
            "productNames",
            "selectedYear",
            "availableYears",
            "start_date",
            "end_date"
        ));
    }

    public function export($start_date, $end_date) 
    {
        return Excel::download(new ListTransactionExportFilter($start_date, $end_date), 'Rekap Transaksi - ' . $start_date . ' s_d ' . $end_date . '.xlsx');
    }
}
