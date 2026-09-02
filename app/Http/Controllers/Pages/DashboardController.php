<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the Executive POS Dashboard.
     */
    public function index()
    {
        $user = Auth::user();

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
