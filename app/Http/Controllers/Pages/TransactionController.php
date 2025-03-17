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

        $post = $request->all();
        $post['total'] = str_replace('.', '', $request->total);
        $post['payment'] = str_replace('.', '', $request->payment);

        $transaction = Transaction::create($post);

        $customers = Customer::where('id', $request->customers_id)->first();
        $products  = Product::where('id', $request->products_id)->first();
        $products->update(['stock' => $products->stock - $customers->limit]);

        $usaha = Usaha::latest()->first();
        $name= $usaha->name ?? config('app.name');
        $address = $usaha->address ?? "-";
        $footer = $usaha->footer ?? "-";
        $thermal = $usaha->name_of_thermal ?? "POS-80";
        $logoPath = public_path($usaha->image ?? "");

        try {
            $connector = new WindowsPrintConnector($thermal); // Sesuaikan dengan nama printer di komputer Anda
            $printer = new Printer($connector);

            // 🔹 Memuat & mencetak logo
            if (file_exists($logoPath)) {
                $logo = EscposImage::load($logoPath);
                $printer->graphics($logo);
            }

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("$name\n");
            $printer->text("$address\n");
            $printer->text("--------------------------------\n");

            // Detail Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Tanggal transaksi : " . now()->format('d-m-Y H:i') . "\n");
            $printer->text("Status : " . $customers->status . "\n");
            $printer->text("--------------------------------\n");

            // Informasi Penjual
            $printer->text("Nama Penjual : " . ($customers->name ?? "-") . "\n");
            $printer->text("Alamat Penjual : " . ($customers->address ?? "-") . "\n");
            $printer->text("Limit Penjual : " . ($transaction->limit ?? "-") . "\n");
            $printer->text("Harga : Rp " . number_format($products->selling_price, 0, ',', '.') . "\n");

            $printer->text("--------------------------------\n");
            $printer->text("Total : Rp " . number_format($transaction->total, 0, ',', '.') . "\n");

            // Footer
            $printer->text("--------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("$footer\n");

            // Potong kertas
            $printer->feed(2);
            $printer->cut();
            $printer->close();

            return response()->json(["code" => 200, "message" => "Transaksi berhasil disimpan & struk dicetak!"]);
        } catch (\Exception $e) {
            return response()->json(["code" => 500, "error" => "Gagal mencetak: " . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
