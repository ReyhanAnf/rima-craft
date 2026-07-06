<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        return [
            'qty'      => fake()->numberBetween(1, 10),
            'price'    => fake()->randomFloat(2, 10_000, 500_000),
            'subtotal' => fn(array $a) => $a['qty'] * $a['price'],
        ];
    }
}
