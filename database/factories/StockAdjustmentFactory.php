<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Material;
use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 *
 * NOTE: This factory is polymorphic. `adjustable_id` defaults to null and
 * MUST always be overridden when creating records in tests. For example:
 *
 *   $material = Material::factory()->create();
 *   StockAdjustment::factory()->create([
 *       'adjustable_type' => Material::class,
 *       'adjustable_id'   => $material->id,
 *   ]);
 */
class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    public function definition(): array
    {
        return [
            'previous_stock'      => 10,
            'actual_stock'        => 15,
            'quantity_difference' => 5,
            'reason'              => 'Stok opname bulanan',
            'adjustable_type'     => Material::class, // default — override saat digunakan
            'adjustable_id'       => null,            // SELALU override saat digunakan
        ];
    }
}
