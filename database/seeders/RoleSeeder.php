<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'dev-admin',
            'super-admin',
            'owner',
            'operator',
            'customer',
            'reseller'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create a default admin user for testing
        $adminRole = Role::where('name', 'super-admin')->first();
        if ($adminRole) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@rimacraft.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'role' => 'super-admin' // Keep the column in sync for now
                ]
            );

            if (!$admin->hasRole('super-admin')) {
                $admin->roles()->attach($adminRole->id);
            }
        }

        // create dev-admin user
        $devAdminRole = Role::where('name', 'dev-admin')->first();
        if ($devAdminRole) {
            $devAdmin = User::firstOrCreate(
                ['email' => 'andreafirdausr@gmail.com'],
                [
                    'name' => 'Developer Admin',
                    'password' => Hash::make('password'),
                    'role' => 'dev-admin' // Keep the column in sync for now
                ]
            );

            if (!$devAdmin->hasRole('dev-admin')) {
                $devAdmin->roles()->attach($devAdminRole->id);
            }
        }
    }
}
