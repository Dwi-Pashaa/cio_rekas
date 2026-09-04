<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Usaha;
use App\Services\DashboardService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;
    protected TransactionService $transactionService;

    public function __construct(DashboardService $dashboardService, TransactionService $transactionService)
    {
        $this->dashboardService = $dashboardService;
        $this->transactionService = $transactionService;
    }

    /**
     * Display the Executive POS Dashboard or Agent Self-Service POS.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Jika User adalah Agent (memiliki role 'Agent' atau hanya memiliki permission 'transaksi mandiri' dan bukan Admin)
        $isAgentUser = $user && ($user->hasRole('Agent') || ($user->can('transaksi mandiri') && !$user->hasRole('Admin')));

        if ($isAgentUser) {
            $customer = Customer::with(['product.branch', 'type', 'status'])
                ->where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->orWhere('code', $user->username)
                ->first();

            $sumProduct = null;
            $userBranch = null;
            $allowedMethods = ['cash', 'transfer'];
            $branches = Branch::all();
            $branchStockMap = [];
            $defaultBranchId = null;

            if ($customer) {
                $customer->amount = $customer->product->selling_price ?? 0;
                $customer->total = floatval($customer->product->selling_price ?? 0) * intval($customer->limit ?? 1);
                $customer->type_name = optional($customer->type)->name ?? 'Reguler';
                $customer->status_name = optional($customer->status)->name ?? 'Aktif';

                $allowedMethods = (array) ($customer->payment_methods ?? ['cash', 'transfer']);

                // Petakan stok produk agent di setiap cabang
                if ($customer->product) {
                    $productName = $customer->product->name;
                    foreach ($branches as $br) {
                        $stk = Product::where('branch_id', $br->id)
                            ->where('name', $productName)
                            ->value('stock') ?? 0;
                        $branchStockMap[$br->id] = (int) $stk;
                    }
                }

                $defaultBranchId = $user->branch_id ?? (optional($customer->product)->branch_id ?? optional($branches->first())->id);
                $sumProduct = $branchStockMap[$defaultBranchId] ?? 0;

                $curBranch = $branches->firstWhere('id', $defaultBranchId);
                $userBranch = $curBranch ? $curBranch->name : 'Cabang';
            }

            $usaha = Usaha::latest()->first();

            return view("pages.dashboard-agent", compact(
                'customer',
                'sumProduct',
                'userBranch',
                'branches',
                'branchStockMap',
                'defaultBranchId',
                'allowedMethods',
                'usaha'
            ));
        }

        // 2. Executive Dashboard POS (Admin & Kasir Reguler)
        $kpiMetrics = $this->dashboardService->getKpiMetrics($user);
        $recentTransactions = $this->dashboardService->getRecentTransactions($user, 5);
        $monthlyTrend = $this->dashboardService->getMonthlySalesTrend($user);
        $topProducts = $this->dashboardService->getTopSellingProducts($user, 5);

        $months = [
            "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
            "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
        ];

        $salesTrend = [
            'data'   => $monthlyTrend,
            'months' => $months
        ];

        return view("pages.dashboard", compact(
            'kpiMetrics',
            'recentTransactions',
            'monthlyTrend',
            'salesTrend',
            'topProducts',
            'months'
        ));
    }
}
