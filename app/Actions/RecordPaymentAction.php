<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class RecordPaymentAction
{
    /**
     * Record a payment (installment/full) with all side effects:
     * - Create payment record
     * - Create cash ledger entry
     * - Update account balance
     * - Update payable's payment_status (unpaid/partial/paid)
     *
     * @param array<string, mixed> $data Validated payment data
     * @return Payment The created payment
     * @throws \Exception
     */
    public function handle(array $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $account = Account::lockForUpdate()->findOrFail(1);
            $payableClass = 'App\\Models\\' . $data['payable_type'];

            /** @var \Illuminate\Database\Eloquent\Model $payable */
            $payable = $payableClass::findOrFail($data['payable_id']);

            $txType = '';
            $descPrefix = '';

            if ($data['payable_type'] === 'Sale') {
                $txType = 'in';
                $descPrefix = 'Pembayaran Penjualan #';
            } elseif ($data['payable_type'] === 'Purchase') {
                $txType = 'out';
                $descPrefix = 'Pembayaran Pembelian #';
                if ($account->balance < $data['amount']) {
                    throw new \Exception("Saldo kas '{$account->name}' tidak mencukupi!");
                }
            } elseif ($data['payable_type'] === 'Production') {
                $txType = 'out';
                $descPrefix = 'Biaya Produksi #';
                if ($account->balance < $data['amount']) {
                    throw new \Exception("Saldo kas '{$account->name}' tidak mencukupi!");
                }
            }

            $balanceAfter = $txType === 'in'
                ? $account->balance + $data['amount']
                : $account->balance - $data['amount'];

            $payment = Payment::create([
                'account_id' => $account->id,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'payable_type' => $payableClass,
                'payable_id' => $payable->id,
            ]);

            CashLedger::create([
                'account_id' => $account->id,
                'payment_label' => 'Cash',
                'date' => $data['date'],
                'type' => $txType,
                'amount' => $data['amount'],
                'balance_after' => $balanceAfter,
                'description' => $descPrefix . $payable->id,
                'reference_type' => get_class($payment),
                'reference_id' => $payment->id,
            ]);

            $account->balance = $balanceAfter;
            $account->save();

            // Update payable's payment_status
            $totalPaid = $payable->payments()->sum('amount');
            $grandTotal = $payable->grand_total ?? $payable->grand_total_cost ?? $payable->total_amount;

            if (isset($payable->payment_status)) {
                if ($totalPaid >= $grandTotal) {
                    $payable->update(['payment_status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $payable->update(['payment_status' => 'partial']);
                }
            }

            return $payment;
        });
    }
}
