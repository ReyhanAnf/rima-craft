<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->word() . ' bahan',
            'unit'           => fake()->randomElement(['kg', 'meter', 'pcs', 'roll']),
            'current_stock'  => fake()->numberBetween(20, 200),
            'last_buy_price' => fake()->randomFloat(2, 1_000, 50_000),
            'min_stock'      => 5,
        ];
    }
}
