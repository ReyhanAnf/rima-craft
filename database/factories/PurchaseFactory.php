<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'date'           => now()->format('Y-m-d'),
            'total_amount'   => fake()->randomFloat(2, 50_000, 500_000),
            'payment_status' => 'unpaid',
        ];
    }
}
