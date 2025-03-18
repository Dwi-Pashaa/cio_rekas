<?php

namespace App\Http\Controllers\Pages;

use App\Exports\Transaction\ListTransactionExportFilter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        return view("pages.finance.index", compact('transaction'));
    }

    public function export($start_date, $end_date) 
    {
        return Excel::download(new ListTransactionExportFilter($start_date, $end_date), 'Rekap Transaksi - ' . $start_date . $end_date . '.xlsx');
    }
}
