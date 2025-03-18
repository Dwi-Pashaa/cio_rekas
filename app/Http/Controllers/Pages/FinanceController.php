<?php

namespace App\Http\Controllers\Pages;

use App\Exports\Transaction\ListTransactionExportFilter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->start_date ?? null;
        $end_date   = $request->end_date ?? null;

        $transaction = collect(); 

        if (!empty($start_date) && !empty($end_date)) {
            $transaction = Transaction::with(['customer', 'product'])
                ->whereBetween('created_at', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay()
                ])
                ->get();
        }

        $incomePerMonth = Transaction::select(
                                DB::raw('MONTH(created_at) as month'),
                                DB::raw('SUM(total) as total_income') 
                            )
                            ->groupBy('month')
                            ->orderBy('month', 'ASC')
                            ->get();

        $productsSoldPerMonth = Transaction::join('products', 'transactions.products_id', '=', 'products.id')
                            ->select(
                                DB::raw('MONTH(transactions.created_at) as month'),
                                'products.name as product_name',
                                DB::raw('SUM(transactions.qty) as total_sold')
                            )
                            ->groupBy('month', 'products.name')
                            ->orderBy('month', 'ASC')
                            ->get();

        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        $incomeData = array_fill(0, 12, 0);

        foreach ($incomePerMonth as $income) {
            $incomeData[$income->month - 1] = $income->total_income;
        }

        $productsPerMonth = [];
        $productNames = [];

        foreach ($productsSoldPerMonth as $product) {
            $monthIndex = $product->month - 1;
            $productsPerMonth[$product->product_name][$monthIndex] = $product->total_sold;
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

        return view("pages.finance.index", compact("transaction", "months", "incomeData", "productsPerMonth", "productNames"));
    }

    public function export($start_date, $end_date) 
    {
        return Excel::download(new ListTransactionExportFilter($start_date, $end_date), 'Rekap Transaksi - ' . $start_date . $end_date . '.xlsx');
    }
}
