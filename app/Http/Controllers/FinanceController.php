<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordTransactionAction;
use App\Http\Requests\Finance\StoreAccountRequest;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Models\Account;
use App\Repositories\FinanceRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FinanceController extends Controller
{
    public function __construct(
        private readonly FinanceRepository $financeRepo,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $accountId = $request->filled('account_id') ? (int) $request->account_id : null;
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Filter by transaction type
        $type = $request->input('type');
        
        $data = $this->financeRepo->getLedgerReport($accountId, $startDate, $endDate, $type);

        return Inertia::render('Finance/Index', [
            'accounts' => $data['accounts'],
            'ledgers' => $data['ledgers'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'totalIncome' => $data['totalIncome'],
            'totalExpense' => $data['totalExpense'],
            'netCashFlow' => $data['netCashFlow'],
            'breakdowns' => $data['breakdowns'],
            'filters' => $request->only(['account_id', 'start_date', 'end_date', 'type']),
        ]);
    }

    public function printReport(Request $request)
    {
        $accountId = $request->filled('account_id') ? (int) $request->account_id : null;
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $data = $this->financeRepo->getPrintReport($accountId, $startDate, $endDate);

        return view('finance.print', $data);
    }

    public function storeAccount(StoreAccountRequest $request)
    {
        $validated = $request->validated();

        Account::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['balance'] ?? 0,
        ]);

        return redirect()->route('finance.index')
                         ->with('success', 'Rekening Kas berhasil ditambahkan!');
    }

    public function storeTransaction(StoreTransactionRequest $request)
    {
        try {
            (new RecordTransactionAction)->handle($request->validated());

            return redirect()->route('finance.index')
                             ->with('success', 'Transaksi kas berhasil dicatat!');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
