<?php

namespace App\Exports\Transaction;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ListTransactionExport implements FromView
{
    /**
    * @return \Illuminate\Contracts\View\View
    */
    public function view(): View
    {
        return view('export.transaction-all', [
            'transaction' => Transaction::with(['customer', 'product'])->orderBy('id', 'DESC')->get()
        ]);
    }
}
