<?php

namespace App\Http\Controllers\Pages;

use App\Exports\Transaction\ListTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Usaha;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $transaction = Transaction::with(['customer', 'product', 'casier'])
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
                        ->paginate($sort);

        $incomePerMonth = Transaction::select(
                            DB::raw('MONTH(created_at) as month'),
                            DB::raw('SUM(total) as total_income')
                        )
                        ->groupBy('month')
                        ->orderBy('month', 'ASC')
                        ->get();

        $topCustomers = Customer::leftJoin('transactions', 'transactions.customers_id', '=', 'customers.id')
                        ->select(
                            'customers.name as customer_name',
                            DB::raw('COALESCE(SUM(transactions.qty), 0) as total_spent')
                        )
                        ->groupBy('customers.id', 'customers.name')
                        ->orderByDesc('total_spent')
                        ->get();

        // $topCustomers = Transaction::join('customers', 'transactions.customers_id', '=', 'customers.id')
        //                 ->select(
        //                     'customers.name as customer_name',
        //                     DB::raw('SUM(transactions.qty) as total_spent')
        //                 )
        //                 ->groupBy('customers.id', 'customers.name')
        //                 ->orderByDesc('total_spent')
        //                 ->get();
                    

        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        $incomeData = array_fill(0, 12, 0);

        foreach ($incomePerMonth as $income) {
            $incomeData[$income->month - 1] = $income->total_income;
        }

        return view("pages.transaction.index", compact("transaction", "months", "incomeData", "topCustomers"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usaha = Usaha::latest()->first();

        if (!$usaha) {
            return redirect()->route('usaha.index')->with('warning', 'Isi detail usaha terlebih dahulu.');
        }

        return view("pages.transaction.create");
    }

    public function getCustomerBySerialNumber(Request $request) 
    {
        $code = $request->code;
        
        $customers = Customer::with(['product'])->where('code', $code)->first();

        if (!$customers) {
            return response()->json(['code' => 404, 'status' => false, 'message' => 'Pelanggan tidak ditemukan.']);
        }

        $customers->amount = $customers->product->selling_price;
        $customers->total = floatval($customers->product->selling_price) * intval($customers->limit);

        return response()->json(['code' => 200, 'status' => true, 'data' => $customers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "payment" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json(['code' => 400, 'errors' => $validation->errors()]);
        }

        $customers = Customer::where('id', $request->customers_id)->first();
        $products  = Product::where('id', $request->products_id)->first();

        if ($products->stock <= 0) {
            return response()->json(['code' => 401, 'status' => 'warning', 'message' => 'Stock barang tidak mencukupi.']);
        }

        $post = $request->all();
        $post['total'] = str_replace('.', '', $request->total);
        $post['payment'] = str_replace('.', '', $request->payment);
        $post['users_id'] = Auth::user()->id;

        $transaction = Transaction::create($post);

        $products->update(['stock' => $products->stock - $customers->limit]);
        
        return response()->json(['code' => 200, 'status' => 'success', 'transaction' => $transaction]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'customer.type', 'customer.status', 'product'])->find($id);
        $usaha = Usaha::latest()->first();
        return view("pages.transaction.receipt", compact("transaction", "usaha"));
    }

    /**
     * Export Excel resource from storage.
     */
    public function export()
    {
        return Excel::download(new ListTransactionExport, 'List Transaksi.xlsx');
    }

    /**
     * Display a listing of the resource.
     */
    public function chart(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $incomePerMonth = Transaction::select(
                            DB::raw('MONTH(created_at) as month'),
                            DB::raw('SUM(total) as total_income')
                        )
                        ->groupBy('month')
                        ->orderBy('month', 'ASC')
                        ->get();

        $topCustomers = Customer::leftJoin('transactions', 'transactions.customers_id', '=', 'customers.id')
                        ->select(
                            'customers.name as customer_name',
                            DB::raw('COALESCE(SUM(transactions.qty), 0) as total_spent')
                        )
                        ->groupBy('customers.id', 'customers.name')
                        ->orderByDesc('total_spent')
                        ->get();


        $months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        $incomeData = array_fill(0, 12, 0);

        foreach ($incomePerMonth as $income) {
            $incomeData[$income->month - 1] = $income->total_income;
        }

        return view("pages.transaction.chart.index", compact("months", "incomeData", "topCustomers"));
    }
}
