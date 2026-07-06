<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Account;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait CreatesTestData
{
    /**
     * Create an admin user with the 'dev-admin' role attached.
     */
    protected function createAdminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'dev-admin']);
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Create a user with the given role attached (role created via firstOrCreate).
     */
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Create a user with a unique role that has the given permission attached.
     */
    protected function createUserWithPermission(string $permissionName): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'operator-test-' . $permissionName]);
        $permission = Permission::firstOrCreate(['name' => $permissionName]);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id);

        return $user;
    }

    /**
     * Create an Account named 'Kas Utama' with the given balance.
     */
    protected function createAccount(float $balance = 1_000_000): Account
    {
        return Account::factory()->create([
            'balance' => $balance,
            'name'    => 'Kas Utama',
        ]);
    }
}
