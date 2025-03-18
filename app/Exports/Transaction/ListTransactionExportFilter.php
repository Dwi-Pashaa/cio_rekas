<?php

namespace App\Exports\Transaction;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class ListTransactionExportFilter implements FromView
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date = null, $end_date = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
    * @return \Illuminate\Contracts\View\View
    */
    public function view(): View
    {
        $transaction = collect(); 

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $transaction = Transaction::with(['customer', 'product'])
                ->whereBetween('created_at', [
                    Carbon::parse($this->start_date)->startOfDay(),
                    Carbon::parse($this->end_date)->endOfDay()
                ])
                ->orderBy('id', 'DESC')
                ->get();
        }

        return view('export.transaction-all', compact('transaction'));
    }
}
