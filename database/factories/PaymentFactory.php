<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 *
 * NOTE: This factory is polymorphic. `payable_id` defaults to null and MUST
 * always be overridden when creating records in tests. For example:
 *
 *   $sale = Sale::factory()->create();
 *   Payment::factory()->create([
 *       'payable_type' => Sale::class,
 *       'payable_id'   => $sale->id,
 *   ]);
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'date'         => now()->format('Y-m-d'),
            'amount'       => fake()->randomFloat(2, 10_000, 500_000),
            'payable_type' => Sale::class, // default — override saat digunakan
            'payable_id'   => null,        // SELALU override saat digunakan
        ];
    }
}
