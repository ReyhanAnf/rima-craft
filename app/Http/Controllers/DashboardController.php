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
        $financials = $this->dashboardRepo->getFinancialMetrics($startStr, $endStr);
        $outstanding = $this->dashboardRepo->getOutstandingTotals();
        $operational = $this->dashboardRepo->getOperationalCounts($startStr, $endStr);
        $lowStock = $this->dashboardRepo->getLowStockMaterials();
        $invoices = $this->dashboardRepo->getOutstandingInvoices();
        $leaderboards = $this->dashboardRepo->getLeaderboards($startStr, $endStr);
        $chartData = $this->dashboardRepo->getChartData($startDate, $endDate);
        $recentSales = $this->dashboardRepo->getRecentSales($startStr, $endStr);

        // Flatten arrays for view compatibility
        extract($financials);
        extract($outstanding);
        extract($operational);
        extract($lowStock);
        extract($invoices);
        extract($leaderboards);

        return view('dashboard', compact(
            'range', 'startDate', 'endDate',
            'totalSales', 'totalPurchases', 'grossProfit', 'cashInflow', 'cashOutflow',
            'totalKas', 'totalReceivables', 'totalPayables',
            'pendingProductions', 'completedProductions',
            'lowStockMaterialsLimit', 'lowStockMaterialsCount',
            'outstandingSales', 'outstandingPurchases',
            'topCustomers', 'topSuppliers', 'topProducts',
            'chartData', 'recentSales',
        ));
    }
}
