<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    public function definition(): array
    {
        return [
            'qty'   => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 1_000, 100_000),
        ];
    }
}
