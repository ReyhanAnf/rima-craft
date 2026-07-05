<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Account;
use App\Models\CashLedger;
use Illuminate\Database\Eloquent\Collection;

class FinanceRepository
{
    /**
     * Get cash ledger entries with optional filters.
     *
     * @return array{ledgers: \Illuminate\Contracts\Pagination\LengthAwarePaginator, totalIncome: float, totalExpense: float, netCashFlow: float}
     */
    public function getLedgerReport(?int $accountId, string $startDate, string $endDate, ?string $type = null): array
    {
        // Consolidate: accounts is no longer needed, but we pass Kas Utama (ID 1) as accounts for compatibility
        $accounts = Account::where('id', 1)->get();
        $mainAccount = Account::find(1);

        $query = CashLedger::with('account')->orderByDesc('date')->orderByDesc('id');

        $query->whereBetween('date', [$startDate, $endDate]);
        
        // Filter by transaction type
        if ($type) {
            $query->where('type', $type);
        }

        $summaryQuery = clone $query;
        $totalIncome = (float) (clone $summaryQuery)->where('type', 'in')->sum('amount');
        $totalExpense = (float) (clone $summaryQuery)->where('type', 'out')->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;

        // Financial category breakdowns
        $breakdownQuery = clone $summaryQuery;
        $breakdowns = $breakdownQuery->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Breakdown based on payment labels (BCA, Cash, COD, etc.)
        $labelBreakdown = CashLedger::selectRaw("
            payment_label,
            SUM(CASE WHEN type = 'in' THEN amount ELSE -amount END) as net_amount
        ")
        ->groupBy('payment_label')
        ->pluck('net_amount', 'payment_label')
        ->toArray();

        // Monthly Stats (Last 6 Months) for analytical charts
        $monthlyStats = CashLedger::selectRaw("
            DATE_FORMAT(date, '%Y-%m') as month,
            SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as expense
        ")
        ->where('date', '>=', now()->subMonths(5)->startOfMonth()->format('Y-m-d'))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();

        $ledgers = $query->paginate(30);

        return compact(
            'accounts', 'mainAccount', 'ledgers', 'startDate', 'endDate', 
            'totalIncome', 'totalExpense', 'netCashFlow', 'breakdowns', 
            'labelBreakdown', 'monthlyStats'
        );
    }

    /**
     * Get ledger entries for printing (no pagination).
     *
     * @return array{ledgers: Collection, totalIncome: float, totalExpense: float, netCashFlow: float, account: Account|null}
     */
    public function getPrintReport(?int $accountId, string $startDate, string $endDate): array
    {
        $query = CashLedger::with('account')->orderBy('date', 'asc')->orderBy('id', 'asc');

        $account = null;
        if ($accountId) {
            $query->where('account_id', $accountId);
            $account = Account::find($accountId);
        }

        $query->whereBetween('date', [$startDate, $endDate]);

        $ledgers = $query->get();
        $totalIncome = (float) $ledgers->where('type', 'in')->sum('amount');
        $totalExpense = (float) $ledgers->where('type', 'out')->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;

        return compact('ledgers', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'netCashFlow', 'account');
    }
}
