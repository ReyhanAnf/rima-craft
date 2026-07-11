<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Account;
use App\Models\CashLedger;
use App\Models\Material;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionArtisanWage;
use App\Models\ProductionMaterial;
use App\Models\ProductionResult;
use Illuminate\Support\Facades\DB;

class RecordProductionAction
{
    /**
     * Record a new production process with all side effects:
     * - Validate material stock availability
     * - Calculate material cost (HPP)
     * - Create production + materials + results
     * - Reduce material stock
     * - Increase product stock
     * - Record cost breakdown to cash ledger (material HPP, labor, overhead)
     *
     * @param array<string, mixed> $data Validated production data
     * @return Production The created production
     * @throws \Exception
     */
    public function handle(array $data): Production
    {
        return DB::transaction(function () use ($data): Production {
            // 1. Validate material stock
            $matQtys = [];
            foreach ($data['materials'] as $mat) {
                $mid = $mat['material_id'];
                if (!isset($matQtys[$mid])) {
                    $matQtys[$mid] = 0;
                }
                $matQtys[$mid] += $mat['qty'];
            }

            foreach ($matQtys as $mid => $totalQty) {
                $material = Material::lockForUpdate()->findOrFail($mid);
                if ($material->current_stock < $totalQty) {
                    throw new \Exception("Stok bahan baku '{$material->name}' tidak mencukupi. Sisa: {$material->current_stock}, Diminta: {$totalQty}");
                }
            }

            // 2. Calculate material cost (HPP) and reduce stock
            $totalMaterialCost = 0;
            $materialsData = [];

            foreach ($data['materials'] as $mat) {
                $material = Material::find($mat['material_id']);
                $costPerUnit = $material->last_buy_price ?? 0;
                $subtotal = $mat['qty'] * $costPerUnit;
                $totalMaterialCost += $subtotal;

                $materialsData[] = [
                    'material_id'  => $material->id,
                    'qty'          => $mat['qty'],
                    'cost_per_unit' => $costPerUnit,
                    'subtotal'     => $subtotal,
                ];

                $material->current_stock -= $mat['qty'];
                $material->save();
            }

            $artisanWageRows = collect($data['artisan_wages'] ?? [])
                ->filter(fn (array $row): bool => !empty($row['artisan_id']) && (float) ($row['amount'] ?? 0) > 0)
                ->values();
            $artisanWageTotal = (float) $artisanWageRows->sum(fn (array $row): float => (float) $row['amount']);

            $laborCost    = (float) ($data['labor_cost'] ?? 0) + $artisanWageTotal;
            $overheadCost = (float) ($data['overhead_cost'] ?? 0);
            $additionalCost = (float) ($data['additional_cost'] ?? 0);
            $grandTotalCost = $totalMaterialCost + $laborCost + $overheadCost + $additionalCost;

            // 3. Create production header
            $production = Production::create([
                'date'                => $data['date'],
                'labor_cost'          => $laborCost,
                'overhead_cost'       => $overheadCost,
                'additional_cost'     => $additionalCost,
                'total_material_cost' => $totalMaterialCost,
                'grand_total_cost'    => $grandTotalCost,
                'notes'               => $data['notes'] ?? null,
                'status'              => 'completed',
            ]);

            // Insert materials
            foreach ($materialsData as $matData) {
                ProductionMaterial::create(array_merge($matData, ['production_id' => $production->id]));
            }

            foreach ($artisanWageRows as $row) {
                ProductionArtisanWage::create([
                    'production_id' => $production->id,
                    'artisan_id' => $row['artisan_id'],
                    'amount' => (float) $row['amount'],
                    'work_description' => $row['work_description'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ]);
            }

            // 4. Calculate allocated cost per unit for products
            $totalProductsQty = (int) array_sum(array_column($data['products'], 'qty'));
            $allocatedCostPerUnit = $totalProductsQty > 0 ? ($grandTotalCost / $totalProductsQty) : 0;

            foreach ($data['products'] as $prod) {
                ProductionResult::create([
                    'production_id'          => $production->id,
                    'product_id'             => $prod['product_id'],
                    'qty'                    => $prod['qty'],
                    'allocated_cost_per_unit' => $allocatedCostPerUnit,
                ]);

                $product = Product::find($prod['product_id']);
                $product->current_stock += $prod['qty'];
                $product->save();
            }

            // 5. Record all production costs to cash ledger for financial reporting
            $account = Account::first();
            if ($account) {
                $runningBalance = $account->balance;

                // 5a. Record material HPP
                if ($totalMaterialCost > 0) {
                    $runningBalance -= $totalMaterialCost;
                    CashLedger::create([
                        'account_id'     => $account->id,
                        'payment_label'  => 'Cash',
                        'date'           => $production->date,
                        'type'           => 'out',
                        'category'       => CashLedger::CATEGORY_PRODUCTION_MATERIAL,
                        'amount'         => $totalMaterialCost,
                        'balance_after'  => $runningBalance,
                        'description'    => 'HPP Bahan Baku Produksi #' . $production->id,
                        'reference_type' => Production::class,
                        'reference_id'   => $production->id,
                    ]);
                }

                // 5b. Record labor cost (upah)
                if ($laborCost > 0) {
                    $runningBalance -= $laborCost;
                    CashLedger::create([
                        'account_id'     => $account->id,
                        'payment_label'  => 'Cash',
                        'date'           => $production->date,
                        'type'           => 'out',
                        'category'       => CashLedger::CATEGORY_PRODUCTION_LABOR,
                        'amount'         => $laborCost,
                        'balance_after'  => $runningBalance,
                        'description'    => 'Upah Tenaga Kerja Produksi #' . $production->id,
                        'reference_type' => Production::class,
                        'reference_id'   => $production->id,
                    ]);
                }

                // 5c. Record overhead cost
                if ($overheadCost > 0) {
                    $runningBalance -= $overheadCost;
                    CashLedger::create([
                        'account_id'     => $account->id,
                        'payment_label'  => 'Cash',
                        'date'           => $production->date,
                        'type'           => 'out',
                        'category'       => CashLedger::CATEGORY_PRODUCTION_OVERHEAD,
                        'amount'         => $overheadCost,
                        'balance_after'  => $runningBalance,
                        'description'    => 'Biaya Overhead Produksi #' . $production->id,
                        'reference_type' => Production::class,
                        'reference_id'   => $production->id,
                    ]);
                }

                // 5d. Record additional cost if any
                if ($additionalCost > 0) {
                    $runningBalance -= $additionalCost;
                    CashLedger::create([
                        'account_id'     => $account->id,
                        'payment_label'  => 'Cash',
                        'date'           => $production->date,
                        'type'           => 'out',
                        'category'       => CashLedger::CATEGORY_OTHER,
                        'amount'         => $additionalCost,
                        'balance_after'  => $runningBalance,
                        'description'    => 'Biaya Lain-lain Produksi #' . $production->id,
                        'reference_type' => Production::class,
                        'reference_id'   => $production->id,
                    ]);
                }

                // Update account balance to reflect all production costs
                $account->balance = $runningBalance;
                $account->save();
            }

            return $production;
        });
    }
}
