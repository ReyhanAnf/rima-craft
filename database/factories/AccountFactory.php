<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name'    => 'Kas Utama',
            'type'    => 'cash',
            'balance' => 1_000_000.00,
        ];
    }
}
