<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * province_id and city_id are intentionally omitted — they must be set
     * manually via Region::create() or a seeder in the test, because the
     * regions table is populated by a seeder, not a factory.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name'    => fake()->name(),
            'customer_phone'   => fake()->numerify('08##########'),
            'customer_email'   => fake()->safeEmail(),
            'customer_address' => fake()->address(),
            'items'            => [
                [
                    'id'       => 1,
                    'name'     => 'Produk',
                    'qty'      => 1,
                    'price'    => 100_000,
                    'subtotal' => 100_000,
                ],
            ],
            'subtotal'         => 100_000,
            'shipping_cost'    => 0,
            'total'            => 100_000,
            'status'           => 'pending',
            'payment_status'   => 'unpaid',
            'payment_method'   => 'transfer',
            'order_method'     => 'form',
        ];
    }
}
