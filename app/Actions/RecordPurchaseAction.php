<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Contact;
use App\Models\Material;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class RecordPurchaseAction
{
    /**
     * Record a new purchase transaction with all side effects:
     * - Create purchase + items
     * - Optionally save new supplier contact
     * - Update material stock & last buy price
     * - If paid: record payment, cash ledger entry, update account balance
     *
     * @param array<string, mixed> $data Validated purchase data
     * @return Purchase The created purchase
     * @throws \Exception
     */
    public function handle(array $data): Purchase
    {
        return DB::transaction(function () use ($data): Purchase {
            $supplierId = $data['supplier_id'] ?? null;
            $supplierName = $data['supplier_name'] ?? null;
            $supplierPhone = $data['supplier_phone'] ?? null;

            // Save supplier to contacts if requested
            if (empty($supplierId) && !empty($supplierName) && !empty($data['save_supplier'])) {
                $contact = Contact::create([
                    'type' => 'supplier',
                    'name' => $supplierName,
                    'phone' => $supplierPhone,
                ]);
                $supplierId = $contact->id;
            }

            // Calculate total
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['qty'] * $item['price']);
            }

            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'supplier_name' => empty($supplierId) ? $supplierName : null,
                'supplier_phone' => empty($supplierId) ? $supplierPhone : null,
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'payment_status' => $data['payment_status'],
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'material_id' => $item['material_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);

                // Update material stock & price
                $material = Material::findOrFail($item['material_id']);
                $material->current_stock += $item['qty'];
                if ($item['price'] > 0) {
                    $material->last_buy_price = $item['price'];
                }
                $material->save();
            }

            // If paid immediately, record payment and cash ledger
            if ($purchase->payment_status === 'paid') {
                $account = Account::first();
                if ($account) {
                    $payment = Payment::create([
                        'account_id' => $account->id,
                        'date' => $purchase->date,
                        'amount' => $purchase->total_amount,
                        'payable_type' => Purchase::class,
                        'payable_id' => $purchase->id,
                    ]);

                    CashLedger::create([
                        'account_id' => $account->id,
                        'date' => $purchase->date,
                        'type' => 'out',
                        'amount' => $purchase->total_amount,
                        'balance_after' => $account->balance - $purchase->total_amount,
                        'description' => 'Pembayaran Lunas Pembelian #' . $purchase->id,
                        'reference_type' => get_class($payment),
                        'reference_id' => $payment->id,
                    ]);

                    $account->balance -= $purchase->total_amount;
                    $account->save();
                }
            }

            return $purchase;
        });
    }
}
