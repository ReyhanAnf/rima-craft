<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepo,
    ) {}

    public function index(Request $request)
    {
        // 1. Resolve date range
        $range = $request->input('range', 'this_month');
        $dateRange = $this->dashboardRepo->resolveDateRange(
            $range,
            $request->input('start_date'),
            $request->input('end_date'),
        );

        $range = $dateRange['range'];
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        // 2. Gather all data via repository
        $financials = $this->dashboardRepo->getFinancialMetrics($startDate, $endDate);
        $outstanding = $this->dashboardRepo->getOutstandingTotals();
        $operational = $this->dashboardRepo->getOperationalCounts($startStr, $endStr);
        $lowStock = $this->dashboardRepo->getLowStockMaterials();
        $invoices = $this->dashboardRepo->getOutstandingInvoices();
        $leaderboards = $this->dashboardRepo->getLeaderboards($startStr, $endStr);
        $chartData = $this->dashboardRepo->getChartData($startDate, $endDate);
        $recentSales = $this->dashboardRepo->getRecentSales($startDate, $endDate);

        // Flatten arrays for view compatibility
        extract($financials);
        extract($outstanding);
        extract($operational);
        extract($lowStock);
        extract($invoices);
        extract($leaderboards);

        return \Inertia\Inertia::render('Dashboard', [
            'range' => $range,
            'startDate' => $startDate->toIso8601String(),
            'endDate' => $endDate->toIso8601String(),
            'totalSales' => (float) $totalSales,
            'totalPurchases' => (float) $totalPurchases,
            'totalProductionCost' => (float) $totalProductionCost,
            'grossProfit' => (float) $grossProfit,
            'profitMargin' => (float) $profitMargin,
            'productionBreakdown' => $productionBreakdown,
            'cashInflow' => (float) $cashInflow,
            'cashOutflow' => (float) $cashOutflow,
            'totalKas' => (float) $totalKas,
            'totalReceivables' => (float) $totalReceivables,
            'totalPayables' => (float) $totalPayables,
            'pendingProductions' => (int) $pendingProductions,
            'completedProductions' => (int) $completedProductions,
            'lowStockMaterialsCount' => (int) $lowStockMaterialsCount,
            'outstandingSales' => $outstandingSales,
            'outstandingPurchases' => $outstandingPurchases,
            'topCustomers' => $topCustomers,
            'topSuppliers' => $topSuppliers,
            'topProducts' => $topProducts,
            'chartData' => $chartData,
            'recentSales' => $recentSales,
        ]);
    }
}
