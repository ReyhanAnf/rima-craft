<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class AdjustStockAction
{
    /**
     * Record a stock adjustment with side effects:
     * - Create adjustment log
     * - Update the actual stock on the material/product
     *
     * @param array<string, mixed> $data Validated adjustment data
     * @return StockAdjustment The created adjustment
     * @throws \Exception
     */
    public function handle(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data): StockAdjustment {
            $modelClass = $data['adjustable_type'] === 'material' ? Material::class : Product::class;

            /** @var Material|Product $item */
            $item = $modelClass::findOrFail($data['adjustable_id']);

            $previousStock = $item->current_stock;
            $actualStock = (float) $data['actual_stock'];
            $difference = $actualStock - $previousStock;

            if ($difference == 0) {
                throw new \Exception('Tidak ada perubahan stok.');
            }

            $adjustment = StockAdjustment::create([
                'adjustable_type' => $modelClass,
                'adjustable_id' => $item->id,
                'previous_stock' => $previousStock,
                'actual_stock' => $actualStock,
                'quantity_difference' => $difference,
                'reason' => $data['reason'],
                'user_id' => auth()->id(),
            ]);

            $item->update([
                'current_stock' => $actualStock,
            ]);

            return $adjustment;
        });
    }
}
