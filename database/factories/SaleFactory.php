<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'date'            => now()->format('Y-m-d'),
            'total_amount'    => fake()->randomFloat(2, 50_000, 1_000_000),
            'shipping_fee'    => 0,
            'discount'        => 0,
            'grand_total'     => fn(array $a) => $a['total_amount'],
            'payment_status'  => 'unpaid',
            'shipping_status' => 'pending',
        ];
    }
}
