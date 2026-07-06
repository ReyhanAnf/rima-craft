<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CashLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashLedger>
 */
class CashLedgerFactory extends Factory
{
    protected $model = CashLedger::class;

    public function definition(): array
    {
        return [
            'date'          => now()->format('Y-m-d'),
            'type'          => 'in',
            'category'      => 'manual',
            'amount'        => fake()->randomFloat(2, 10_000, 100_000),
            'balance_after' => fake()->randomFloat(2, 100_000, 1_000_000),
            'description'   => fake()->sentence(),
            'payment_label' => 'Cash',
        ];
    }
}
