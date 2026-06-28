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
        $accounts = Account::orderBy('name')->get();
        $query = CashLedger::with('account')->orderByDesc('date')->orderByDesc('id');

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

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

        $ledgers = $query->paginate(30);

        return compact('accounts', 'ledgers', 'startDate', 'endDate', 'totalIncome', 'totalExpense', 'netCashFlow', 'breakdowns');
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
