<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CustomerExport implements FromView
{
    /**
    * @return \Illuminate\Contracts\View\View
    */
    public function view(): View
    {
        return view('export.customer', [
            'customers' => Customer::with(['product', 'type', 'status'])->orderBy('id', 'DESC')->get()
        ]);
    }
}
