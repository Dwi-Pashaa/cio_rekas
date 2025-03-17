<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return "List Transaksi";
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
        $customers->total  = $customers->product->selling_price * $customers->limit;

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

        $transaction = Transaction::create($post);

        $products->update(['stock' => $products->stock - $customers->limit]);
        
        return response()->json(['code' => 200, 'status' => 'success', 'transaction' => $transaction]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'product'])->find($id);
        $usaha = Usaha::latest()->first();
        return view("pages.transaction.receipt", compact("transaction", "usaha"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
