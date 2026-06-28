<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Contact;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

// @see CashLedger::CATEGORY_* constants for category values

class RecordSaleAction
{
    /**
     * Record a new sale transaction with all side effects:
     * - Validate stock availability
     * - Create sale + items
     * - Optionally save new customer contact
     * - Reduce product stock
     * - If paid: record payment, cash ledger entry, update account balance
     *
     * @param array<string, mixed> $data Validated sale data
     * @return Sale The created sale
     * @throws \Exception
     */
    public function handle(array $data): Sale
    {
        return DB::transaction(function () use ($data): Sale {
            $customerId = $data['customer_id'] ?? null;
            $customerName = $data['customer_name'] ?? null;
            $customerPhone = $data['customer_phone'] ?? null;

            // Save customer to contacts if requested
            if (empty($customerId) && !empty($customerName) && !empty($data['save_customer'])) {
                $contact = Contact::create([
                    'type' => 'customer',
                    'name' => $customerName,
                    'phone' => $customerPhone,
                ]);
                $customerId = $contact->id;
            }

            // Validate stock availability
            $productQtys = [];
            foreach ($data['items'] as $item) {
                $pid = $item['product_id'];
                if (!isset($productQtys[$pid])) {
                    $productQtys[$pid] = 0;
                }
                $productQtys[$pid] += $item['qty'];
            }

            foreach ($productQtys as $pid => $totalQty) {
                $product = Product::lockForUpdate()->findOrFail($pid);
                if ($product->current_stock < $totalQty) {
                    throw new \Exception("Stok produk '{$product->name}' tidak mencukupi. Sisa stok: {$product->current_stock}, Diminta: {$totalQty}");
                }
            }

            // Calculate totals
            $totalAmount = 0;
            $itemsData = [];
            foreach ($data['items'] as $item) {
                $subtotal = $item['qty'] * $item['price'];
                $totalAmount += $subtotal;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ];
            }

            $shippingFee = (float) ($data['shipping_fee'] ?? 0);
            $discount = (float) ($data['discount'] ?? 0);
            $grandTotal = $totalAmount + $shippingFee - $discount;

            $sale = Sale::create([
                'customer_id' => $customerId,
                'customer_name' => empty($customerId) ? $customerName : null,
                'customer_phone' => empty($customerId) ? $customerPhone : null,
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'shipping_fee' => $shippingFee,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'payment_status' => $data['payment_status'],
                'shipping_status' => $data['shipping_status'],
            ]);

            foreach ($itemsData as $itemData) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'qty' => $itemData['qty'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // Reduce product stock
                $product = Product::find($itemData['product_id']);
                $product->current_stock -= $itemData['qty'];
                $product->save();
            }

            // If paid immediately, record payment and cash ledger
            if ($sale->payment_status === 'paid') {
                $account = Account::first();
                if ($account) {
                    $payment = Payment::create([
                        'account_id' => $account->id,
                        'date' => $sale->date,
                        'amount' => $sale->grand_total,
                        'payable_type' => Sale::class,
                        'payable_id' => $sale->id,
                    ]);

                    CashLedger::create([
                        'account_id'     => $account->id,
                        'date'           => $sale->date,
                        'type'           => 'in',
                        'category'       => CashLedger::CATEGORY_SALE_INCOME,
                        'amount'         => $sale->grand_total,
                        'balance_after'  => $account->balance + $sale->grand_total,
                        'description'    => 'Pendapatan Penjualan #' . $sale->id,
                        'reference_type' => get_class($payment),
                        'reference_id'   => $payment->id,
                    ]);

                    $account->balance += $sale->grand_total;
                    $account->save();
                }
            }

            return $sale;
        });
    }
}
