<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'           => fake()->words(3, true),
            'base_price'     => fake()->randomFloat(2, 10_000, 500_000),
            'reseller_price' => fake()->randomFloat(2, 8_000, 400_000),
            'current_stock'  => fake()->numberBetween(10, 100),
        ];
    }

    /**
     * State: product with zero stock.
     */
    public function out_of_stock(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_stock' => 0,
        ]);
    }

    /**
     * State: product with a specific reseller price.
     */
    public function with_reseller_price(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'reseller_price' => $price,
        ]);
    }
}
