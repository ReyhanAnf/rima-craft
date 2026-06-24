<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Material;
use App\Models\Product;
use App\Models\Production;
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
                    'material_id' => $material->id,
                    'qty' => $mat['qty'],
                    'cost_per_unit' => $costPerUnit,
                    'subtotal' => $subtotal,
                ];

                $material->current_stock -= $mat['qty'];
                $material->save();
            }

            $additionalCost = (float) ($data['additional_cost'] ?? 0);
            $grandTotalCost = $totalMaterialCost + $additionalCost;

            // 3. Create production header
            $production = Production::create([
                'date' => $data['date'],
                'additional_cost' => $additionalCost,
                'total_material_cost' => $totalMaterialCost,
                'grand_total_cost' => $grandTotalCost,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            // Insert materials
            foreach ($materialsData as $matData) {
                ProductionMaterial::create(array_merge($matData, ['production_id' => $production->id]));
            }

            // 4. Calculate allocated cost per unit for products
            $totalProductsQty = (int) array_sum(array_column($data['products'], 'qty'));
            $allocatedCostPerUnit = $totalProductsQty > 0 ? ($grandTotalCost / $totalProductsQty) : 0;

            foreach ($data['products'] as $prod) {
                ProductionResult::create([
                    'production_id' => $production->id,
                    'product_id' => $prod['product_id'],
                    'qty' => $prod['qty'],
                    'allocated_cost_per_unit' => $allocatedCostPerUnit,
                ]);

                $product = Product::find($prod['product_id']);
                $product->current_stock += $prod['qty'];
                $product->save();
            }

            return $production;
        });
    }
}
