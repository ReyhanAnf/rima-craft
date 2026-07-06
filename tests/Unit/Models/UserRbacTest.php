<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class UserRbacTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    // -------------------------------------------------------------------------
    // hasRole
    // -------------------------------------------------------------------------

    #[Test]
    public function it_has_role_returns_true_when_user_has_role(): void
    {
        // Arrange
        $user = $this->createUserWithRole('editor');

        // Assert
        $this->assertTrue($user->hasRole('editor'));
    }

    #[Test]
    public function it_has_role_returns_false_when_user_does_not_have_role(): void
    {
        // Arrange — plain user with no roles attached
        $user = User::factory()->create();

        // Assert
        $this->assertFalse($user->hasRole('admin'));
    }

    // -------------------------------------------------------------------------
    // hasPermission
    // -------------------------------------------------------------------------

    #[Test]
    public function it_dev_admin_bypasses_all_permission_checks(): void
    {
        // Arrange
        $admin = $this->createAdminUser();

        // Assert — any permission string should return true for dev-admin
        $this->assertTrue($admin->hasPermission('anything'));
    }

    #[Test]
    public function it_has_permission_returns_true_via_role(): void
    {
        // Arrange
        $user = $this->createUserWithPermission('edit-products');

        // Assert
        $this->assertTrue($user->hasPermission('edit-products'));
    }

    #[Test]
    public function it_has_permission_returns_false_when_not_assigned(): void
    {
        // Arrange — plain user with no roles / permissions
        $user = User::factory()->create();

        // Assert
        $this->assertFalse($user->hasPermission('delete-everything'));
    }

    // -------------------------------------------------------------------------
    // hasAnyRole
    // -------------------------------------------------------------------------

    #[Test]
    public function it_has_any_role_returns_true_when_at_least_one_role_matches(): void
    {
        // Arrange — user has 'reseller', which is one of the checked roles
        $user = $this->createUserWithRole('reseller');

        // Assert
        $this->assertTrue($user->hasAnyRole(['admin', 'reseller']));
    }

    #[Test]
    public function it_has_any_role_returns_false_when_no_roles_match(): void
    {
        // Arrange — user has 'customer', which is not in the checked list
        $user = $this->createUserWithRole('customer');

        // Assert
        $this->assertFalse($user->hasAnyRole(['admin', 'reseller']));
    }

    // -------------------------------------------------------------------------
    // hasAllPermissions
    // -------------------------------------------------------------------------

    #[Test]
    public function it_has_all_permissions_returns_true_when_all_assigned(): void
    {
        // Arrange — create user with two permissions via two separate roles
        $user = $this->createUserWithPermission('perm-a');

        // Attach a second role+permission to the same user
        $roleB      = Role::factory()->create(['name' => 'operator-test-perm-b']);
        $permissionB = Permission::firstOrCreate(['name' => 'perm-b']);
        $roleB->permissions()->attach($permissionB->id);
        $user->roles()->attach($roleB->id);

        // Assert
        $this->assertTrue($user->hasAllPermissions(['perm-a', 'perm-b']));
    }

    #[Test]
    public function it_has_all_permissions_returns_false_when_one_is_missing(): void
    {
        // Arrange — user only has 'perm-a', not 'perm-b'
        $user = $this->createUserWithPermission('perm-a');

        // Assert
        $this->assertFalse($user->hasAllPermissions(['perm-a', 'perm-b']));
    }
}
