<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Contact;
use App\Models\Material;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Resolve date range from request input.
     *
     * @return array{range: string, startDate: Carbon, endDate: Carbon}
     */
    public function resolveDateRange(string $range, ?string $startDateInput, ?string $endDateInput): array
    {
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();

        switch ($range) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                break;
            case 'last_7_days':
                $startDate = Carbon::today()->subDays(6)->startOfDay();
                break;
            case 'last_30_days':
                $startDate = Carbon::today()->subDays(29)->startOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear()->startOfDay();
                break;
            case 'custom':
                $startDate = $startDateInput
                    ? Carbon::parse($startDateInput)->startOfDay()
                    : Carbon::now()->startOfMonth()->startOfDay();
                $endDate = $endDateInput
                    ? Carbon::parse($endDateInput)->endOfDay()
                    : Carbon::now()->endOfDay();
                break;
            default:
                $range = 'this_month';
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
        }

        return ['range' => $range, 'startDate' => $startDate, 'endDate' => $endDate];
    }

    /**
     * Get financial metrics for the given date range.
     *
     * @return array{totalSales: float, totalPurchases: float, totalProductionCost: float, grossProfit: float, cashInflow: float, cashOutflow: float, totalKas: float}
     */
    public function getFinancialMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $offlineSales = (float) Sale::whereBetween('date', [$startStr, $endStr])->sum('grand_total');
        $onlinePaidSales = (float) \App\Models\Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
        $onlinePartialSales = (float) \App\Models\Order::where('payment_status', 'partial')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('down_payment_amount');
        $onlineSales = $onlinePaidSales + $onlinePartialSales;
        
        $totalSales = $offlineSales + $onlineSales;
        $totalPurchases = (float) Purchase::whereBetween('date', [$startStr, $endStr])->sum('total_amount');
        
        // Sum of all production costs (materials HPP + labor + overhead)
        $prodMaterial = (float) CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_MATERIAL)
            ->whereBetween('date', [$startStr, $endStr])->sum('amount');
        $prodLabor = (float) CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_LABOR)
            ->whereBetween('date', [$startStr, $endStr])->sum('amount');
        $prodOverhead = (float) CashLedger::where('category', CashLedger::CATEGORY_PRODUCTION_OVERHEAD)
            ->whereBetween('date', [$startStr, $endStr])->sum('amount');
        $totalProductionCost = $prodMaterial + $prodLabor + $prodOverhead;

        // Net gross profit = Sales - Purchases (raw material buys) - Production costs (actually used in production)
        $grossProfit = $totalSales - $totalPurchases - $totalProductionCost;
        $profitMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        $cashInflow = (float) CashLedger::where('type', 'in')->whereBetween('date', [$startStr, $endStr])->sum('amount');
        $cashOutflow = (float) CashLedger::where('type', 'out')->whereBetween('date', [$startStr, $endStr])->sum('amount');

        $totalKas = (float) Account::sum('balance');

        $productionBreakdown = [
            'material' => $prodMaterial,
            'labor' => $prodLabor,
            'overhead' => $prodOverhead,
        ];

        return compact(
            'totalSales', 
            'totalPurchases', 
            'totalProductionCost', 
            'grossProfit', 
            'profitMargin',
            'cashInflow', 
            'cashOutflow', 
            'totalKas', 
            'productionBreakdown'
        );
    }

    /**
     * Get outstanding receivables and payables totals.
     *
     * @return array{totalReceivables: float, totalPayables: float}
     */
    public function getOutstandingTotals(): array
    {
        $totalReceivables = (float) Sale::whereIn('payment_status', ['unpaid', 'partial'])
            ->with('payments')
            ->get()
            ->sum(fn (Sale $sale): float => (float) ($sale->grand_total - $sale->payments->sum('amount')));

        $totalPayables = (float) Purchase::whereIn('payment_status', ['unpaid', 'partial'])
            ->with('payments')
            ->get()
            ->sum(fn (Purchase $purchase): float => (float) ($purchase->total_amount - $purchase->payments->sum('amount')));

        return compact('totalReceivables', 'totalPayables');
    }

    /**
     * Get operational counts.
     *
     * @return array{pendingProductions: int, completedProductions: int}
     */
    public function getOperationalCounts(string $startStr, string $endStr): array
    {
        $pendingProductions = Production::where('status', 'pending')->count();
        $completedProductions = Production::where('status', 'completed')
            ->whereBetween('date', [$startStr, $endStr])
            ->count();

        return compact('pendingProductions', 'completedProductions');
    }

    /**
     * Get low stock materials.
     *
     * @return array{lowStockMaterialsCount: int, lowStockMaterialsLimit: \Illuminate\Database\Eloquent\Collection}
     */
    public function getLowStockMaterials(): array
    {
        $lowStockMaterials = Material::whereColumn('current_stock', '<=', 'min_stock')->get();
        $lowStockMaterialsCount = $lowStockMaterials->count();
        $lowStockMaterialsLimit = $lowStockMaterials->take(5);

        return compact('lowStockMaterialsCount', 'lowStockMaterialsLimit');
    }

    /**
     * Get outstanding invoices (sales & purchases).
     *
     * @return array{outstandingSales: \Illuminate\Database\Eloquent\Collection, outstandingPurchases: \Illuminate\Database\Eloquent\Collection}
     */
    public function getOutstandingInvoices(): array
    {
        $outstandingSales = Sale::with('customer')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function (Sale $sale): Sale {
                $sale->outstanding_amount = (float) ($sale->grand_total - $sale->payments()->sum('amount'));
                return $sale;
            });

        $outstandingPurchases = Purchase::with('supplier')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function (Purchase $purchase): Purchase {
                $purchase->outstanding_amount = (float) ($purchase->total_amount - $purchase->payments()->sum('amount'));
                return $purchase;
            });

        return compact('outstandingSales', 'outstandingPurchases');
    }

    /**
     * Get leaderboard data (top customers, suppliers, products).
     */
    public function getLeaderboards(string $startStr, string $endStr): array
    {
        $topCustomers = Sale::select('customer_id', 'customer_name', DB::raw('SUM(grand_total) as total_spent'))
            ->whereBetween('date', [$startStr, $endStr])
            ->groupBy('customer_id', 'customer_name')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get()
            ->map(function (Sale $sale): Sale {
                if ($sale->customer_id) {
                    $customer = Contact::find($sale->customer_id);
                    $sale->name = $customer->name ?? $sale->customer_name ?? 'Pelanggan Umum';
                    $sale->phone = $customer->phone ?? '-';
                } else {
                    $sale->name = $sale->customer_name ?? 'Pelanggan Umum';
                    $sale->phone = '-';
                }
                return $sale;
            });

        $topSuppliers = Purchase::select('supplier_id', 'supplier_name', DB::raw('SUM(total_amount) as total_received'))
            ->whereBetween('date', [$startStr, $endStr])
            ->groupBy('supplier_id', 'supplier_name')
            ->orderByDesc('total_received')
            ->take(5)
            ->get()
            ->map(function (Purchase $purchase): Purchase {
                if ($purchase->supplier_id) {
                    $supplier = Contact::find($purchase->supplier_id);
                    $purchase->name = $supplier->name ?? $purchase->supplier_name ?? 'Supplier Umum';
                    $purchase->phone = $supplier->phone ?? '-';
                } else {
                    $purchase->name = $purchase->supplier_name ?? 'Supplier Umum';
                    $purchase->phone = '-';
                }
                return $purchase;
            });

        $topProducts = SaleItem::with('product')
            ->whereHas('sale', function ($q) use ($startStr, $endStr): void {
                $q->whereBetween('date', [$startStr, $endStr]);
            })
            ->select('product_id', DB::raw('SUM(qty) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return compact('topCustomers', 'topSuppliers', 'topProducts');
    }

    public function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $diffInDays = $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay());
        $chartData = [
            'categories' => [],
            'sales' => [],
            'purchases' => [],
            'type' => ($diffInDays > 45) ? 'monthly' : 'daily',
        ];

        // 1. Fetch offline sales
        $salesByDate = Sale::whereBetween('date', [$startStr, $endStr])
            ->selectRaw('date, SUM(grand_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // 2. Fetch online sales (paid and partial orders)
        $onlinePaidSalesByDate = \App\Models\Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date_only, SUM(total) as total')
            ->groupBy('date_only')
            ->pluck('total', 'date_only');

        $onlinePartialSalesByDate = \App\Models\Order::where('payment_status', 'partial')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date_only, SUM(down_payment_amount) as total')
            ->groupBy('date_only')
            ->pluck('total', 'date_only');

        $onlineSalesByDate = [];
        foreach ($onlinePaidSalesByDate as $dateStr => $total) {
            $onlineSalesByDate[$dateStr] = (float) $total;
        }
        foreach ($onlinePartialSalesByDate as $dateStr => $total) {
            $onlineSalesByDate[$dateStr] = ($onlineSalesByDate[$dateStr] ?? 0.0) + (float) $total;
        }

        // 3. Merge sales
        $mergedSalesByDate = [];
        foreach ($salesByDate as $dateStr => $total) {
            $mergedSalesByDate[$dateStr] = (float) $total;
        }
        foreach ($onlineSalesByDate as $dateStr => $total) {
            $mergedSalesByDate[$dateStr] = ($mergedSalesByDate[$dateStr] ?? 0.0) + (float) $total;
        }

        $purchasesByDate = Purchase::whereBetween('date', [$startStr, $endStr])
            ->selectRaw('date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        if ($chartData['type'] === 'monthly') {
            $salesByMonth = [];
            foreach ($mergedSalesByDate as $dateStr => $total) {
                $monthStr = Carbon::parse($dateStr)->format('Y-m');
                $salesByMonth[$monthStr] = ($salesByMonth[$monthStr] ?? 0) + $total;
            }
            $purchasesByMonth = [];
            foreach ($purchasesByDate as $dateStr => $total) {
                $monthStr = Carbon::parse($dateStr)->format('Y-m');
                $purchasesByMonth[$monthStr] = ($purchasesByMonth[$monthStr] ?? 0) + $total;
            }

            $current = $startDate->copy()->startOfMonth();
            while ($current->lte($endDate)) {
                $monthKey = $current->format('Y-m');
                $chartData['categories'][] = $current->format('M Y');
                $chartData['sales'][] = (float) ($salesByMonth[$monthKey] ?? 0);
                $chartData['purchases'][] = (float) ($purchasesByMonth[$monthKey] ?? 0);
                $current->addMonth();
            }
        } else {
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $dateKey = $current->toDateString();
                $chartData['categories'][] = $current->format('d M');
                $chartData['sales'][] = (float) ($mergedSalesByDate[$dateKey] ?? 0);
                $chartData['purchases'][] = (float) ($purchasesByDate[$dateKey] ?? 0);
                $current->addDay();
            }
        }

        return $chartData;
    }

    /**
     * Get recent sales for the dashboard, merging offline and online transactions.
     */
    public function getRecentSales(Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $offline = Sale::with('customer')
            ->whereBetween('date', [$startStr, $endStr])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($sale) => [
                'id' => 'offline_' . $sale->id,
                'invoice_number' => $sale->invoice_number,
                'contact' => ['name' => $sale->customer->name ?? $sale->customer_name ?? 'Pelanggan Umum'],
                'sale_date' => $sale->date,
                'total_amount' => (float) $sale->grand_total,
            ]);

        $online = \App\Models\Order::whereIn('payment_status', ['paid', 'partial'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($order) => [
                'id' => 'online_' . $order->id,
                'invoice_number' => $order->order_number,
                'contact' => ['name' => $order->customer_name ?? 'Pelanggan Online'],
                'sale_date' => $order->created_at->toDateString(),
                'total_amount' => (float) $order->total,
            ]);

        return $offline->concat($online)
            ->sortByDesc('sale_date')
            ->take(5)
            ->values();
    }
}
