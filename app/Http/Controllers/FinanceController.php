<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordTransactionAction;
use App\Http\Requests\Finance\StoreAccountRequest;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Models\Account;
use App\Repositories\FinanceRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        private readonly FinanceRepository $financeRepo,
    ) {}

    public function index(Request $request): View
    {
        $accountId = $request->filled('account_id') ? (int) $request->account_id : null;
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        
        // Filter by transaction type
        $type = $request->input('type');
        
        $data = $this->financeRepo->getLedgerReport($accountId, $startDate, $endDate, $type);

        if ($request->header('HX-Target') === 'finance-content') {
            return view('finance.content', $data);
        }

        return view('finance.index', $data);
    }

    public function printReport(Request $request): View
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

        return back()->with('toast', ['message' => 'Rekening Kas berhasil ditambahkan!', 'type' => 'success']);
    }

    public function storeTransaction(StoreTransactionRequest $request)
    {
        try {
            (new RecordTransactionAction)->handle($request->validated());

            return redirect()->route('finance.index')
                             ->with('toast', ['message' => 'Transaksi kas berhasil dicatat!', 'type' => 'success']);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
