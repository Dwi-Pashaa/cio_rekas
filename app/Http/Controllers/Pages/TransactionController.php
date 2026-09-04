<?php

namespace App\Http\Controllers\Pages;

use App\Exports\Transaction\ListTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Usaha;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $user ? ($user->getRoleNames()[0] ?? null) : null;
        $sort = (int)($request->sort ?? 10);
        $search = $request->search ?? null;

        $transaction = $this->transactionService->getTransactionsPaginated($user, $search, $sort);
        $incomeData = $this->transactionService->getMonthlyIncomeData($user);
        $topCustomers = $this->transactionService->getTopCustomers($user);

        $branchStock = null;
        $userBranchName = null;
        $lowStockCount = 0;

        if ($user && !in_array($level, ['Admin', 'Manager']) && $user->branch_id) {
            $branchStock = Product::where('branch_id', $user->branch_id)->sum('stock');
            $userBranchName = $user->branch->name ?? 'Cabang';
            $lowStockCount = Product::where('branch_id', $user->branch_id)->where('stock', '<=', 5)->count();
        }

        $months = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];

        return view("pages.transaction.index", compact(
            "transaction",
            "months",
            "incomeData",
            "topCustomers",
            "branchStock",
            "userBranchName",
            "lowStockCount"
        ));
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

        $user = Auth::user();
        $sumProduct = null;
        $userBranch = null;

        if ($user && $user->branch_id) {
            $sumProduct = (int) Product::where('branch_id', $user->branch_id)->sum('stock');
            $userBranch = $user->branch->name ?? 'Cabang';
        }

        return view("pages.transaction.create", compact("sumProduct", "userBranch"));
    }

    /**
     * Tampilan transaksi khusus via tap NFC Agent (berdasarkan token terenkripsi).
     */
    public function createAgentTransaction(string $token)
    {
        $usaha = Usaha::latest()->first();

        if (!$usaha) {
            return redirect()->route('usaha.index')->with('warning', 'Isi detail usaha terlebih dahulu.');
        }

        // 1. Dekripsi token Serial Number
        $code = Customer::decryptCode($token);

        if (!$code) {
            return redirect()->route('transaksi.create')->with('error', 'Link transaksi NFC tidak valid atau telah kadaluarsa.');
        }

        // 2. Ambil data agent & paket produknya
        $user = Auth::user();
        $customer = $this->transactionService->findCustomerBySerialNumber($code, $user);

        if (!$customer) {
            return redirect()->route('transaksi.create')->with('error', 'Agent dengan Serial Number (' . e($code) . ') tidak ditemukan atau produk belum diatur.');
        }

        // 3. Info stok cabang kasir
        $sumProduct = null;
        $userBranch = null;

        if ($user && $user->branch_id) {
            $sumProduct = (int) Product::where('branch_id', $user->branch_id)->sum('stock');
            $userBranch = $user->branch->name ?? 'Cabang';
        }

        return view("pages.transaction.agent", compact("customer", "sumProduct", "userBranch", "token"));
    }

    /**
     * Lookup customer by serial number via Service.
     */
    public function getCustomerBySerialNumber(Request $request)
    {
        $code = $request->code;
        $customer = $this->transactionService->findCustomerBySerialNumber($code, Auth::user());

        if (!$customer) {
            return response()->json(['code' => 404, 'status' => false, 'message' => 'Pelanggan tidak ditemukan.']);
        }

        return response()->json(['code' => 200, 'status' => true, 'data' => $customer]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "customers_id" => "required",
            "products_id"  => "required",
            "total"        => "required",
        ]);

        if ($validation->fails()) {
            return response()->json(['code' => 400, 'errors' => $validation->errors()]);
        }

        try {
            $user = Auth::user();
            $result = $this->transactionService->processCheckout($request->all(), $user);
            $transaction = $result['transaction'];
            $invoiceUrl = $result['invoice_url'] ?? null;

            // Hitung sisa stok cabang dan sisa stok produk setelah transaksi berhasil secara realtime
            $targetBranchId = $request->branch_id ?? ($user->branch_id ?? $transaction->branch_id);
            $updatedBranchStock = $targetBranchId ? (int) Product::where('branch_id', $targetBranchId)->sum('stock') : 0;

            $deductedProduct = Product::find($transaction->products_id);
            $updatedProductStock = $deductedProduct ? (int) $deductedProduct->stock : 0;

            return response()->json([
                'code'                  => 200,
                'status'                => 'success',
                'transaction'           => $transaction,
                'invoice_url'           => $invoiceUrl,
                'payment_method'        => $transaction->payment_method,
                'payment_status'        => $transaction->payment_status,
                'updated_branch_stock'  => $updatedBranchStock,
                'updated_product_stock' => $updatedProductStock,
                'message'               => 'Pesanan berhasil diproses dan stok cabang otomatis terpotong!'
            ]);
        } catch (Exception $e) {
            return response()->json(['code' => 400, 'status' => 'warning', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint API untuk pengecekan sisa stok cabang terkini secara realtime.
     */
    public function getCurrentBranchStock()
    {
        $user = Auth::user();
        if (!$user || !$user->branch_id) {
            return response()->json(['status' => false, 'stock' => 0]);
        }

        $stock = (int) Product::where('branch_id', $user->branch_id)->sum('stock');
        return response()->json([
            'status' => true,
            'stock'  => $stock,
            'branch' => optional($user->branch)->name ?? 'Cabang'
        ]);
    }

    /**
     * Display the specified resource for receipt printing.
     */
    public function show(string $id)
    {
        $data = $this->transactionService->getReceiptData($id);
        return view("pages.transaction.receipt", [
            'transaction' => $data['transaction'],
            'usaha'       => $data['usaha']
        ]);
    }

    /**
     * Tampilan struk invoice publik yang dapat diakses langsung via link WhatsApp / Email.
     */
    public function publicReceipt(string $id)
    {
        $data = $this->transactionService->getReceiptData($id);
        return view("pages.transaction.receipt", [
            'transaction' => $data['transaction'],
            'usaha'       => $data['usaha']
        ]);
    }

    /**
     * Export Excel resource from storage.
     */
    public function export()
    {
        return Excel::download(new ListTransactionExport, 'List Transaksi.xlsx');
    }

    /**
     * Display chart analytics.
     */
    public function chart(Request $request)
    {
        $user = Auth::user();
        $selectedYear = (int) ($request->year ?? date('Y'));
        $incomeData = $this->transactionService->getMonthlyIncomeData($user, $selectedYear);
        $topCustomers = $this->transactionService->getTopCustomers($user, 10, $selectedYear);

        $availableYears = Transaction::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->pluck('year')
            ->toArray();

        if (!in_array((int) date('Y'), $availableYears)) {
            array_unshift($availableYears, (int) date('Y'));
        }

        $months = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        ];

        return view("pages.transaction.chart.index", compact("months", "incomeData", "topCustomers", "selectedYear", "availableYears"));
    }
}
