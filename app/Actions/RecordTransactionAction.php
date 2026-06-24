<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\CashLedger;
use Illuminate\Support\Facades\DB;

class RecordTransactionAction
{
    /**
     * Record a manual cash transaction with side effects:
     * - Create cash ledger entry
     * - Update account balance
     *
     * @param array<string, mixed> $data Validated transaction data
     * @return CashLedger The created ledger entry
     * @throws \Exception
     */
    public function handle(array $data): CashLedger
    {
        return DB::transaction(function () use ($data): CashLedger {
            $account = Account::lockForUpdate()->findOrFail($data['account_id']);

            if ($data['type'] === 'out' && $account->balance < $data['amount']) {
                throw new \Exception("Saldo kas '{$account->name}' tidak mencukupi!");
            }

            $balanceAfter = $data['type'] === 'in'
                ? $account->balance + $data['amount']
                : $account->balance - $data['amount'];

            $ledger = CashLedger::create([
                'account_id' => $account->id,
                'date' => $data['date'],
                'type' => $data['type'],
                'amount' => $data['amount'],
                'balance_after' => $balanceAfter,
                'description' => $data['description'],
            ]);

            $account->balance = $balanceAfter;
            $account->save();

            return $ledger;
        });
    }
}
