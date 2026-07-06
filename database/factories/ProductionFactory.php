<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Production;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Production>
 */
class ProductionFactory extends Factory
{
    protected $model = Production::class;

    public function definition(): array
    {
        return [
            'date'                => now()->format('Y-m-d'),
            'labor_cost'          => 0,
            'overhead_cost'       => 0,
            'additional_cost'     => 0,
            'total_material_cost' => 0,
            'grand_total_cost'    => 0,
            'status'              => 'completed',
        ];
    }
}
