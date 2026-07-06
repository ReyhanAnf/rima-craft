<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

/**
 * Feature tests for admin order management.
 *
 * Covers: view orders with/without permission, update order status,
 * delete order with/without permission.
 *
 * Validates: Requirements 15.1, 15.2, 15.3, 15.4, 15.5
 */
class AdminOrderManagementTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private User $viewOrdersUser;
    private User $manageOrdersUser;
    private User $noPermissionUser;
    private Region $province;
    private Region $city;
    /** @var Order[] */
    private array $orders;

    protected function setUp(): void
    {
        parent::setUp();

        // User with only 'view-orders' permission
        $this->viewOrdersUser = $this->createUserWithPermission('view-orders');

        // User with both 'view-orders' and 'manage-orders' permissions
        $this->manageOrdersUser = $this->createUserWithPermission('view-orders');
        $manageRole = Role::factory()->create(['name' => 'manage-orders-role-' . uniqid()]);
        $managePermission = \App\Models\Permission::firstOrCreate(['name' => 'manage-orders']);
        $manageRole->permissions()->attach($managePermission->id);
        $this->manageOrdersUser->roles()->attach($manageRole->id);

        // User with no relevant permissions
        $this->noPermissionUser = User::factory()->create();

        // Create a Region (province + city) required by OrderFactory
        $this->province = Region::create([
            'code'      => 'PROV-TEST-' . uniqid(),
            'parent_id' => null,
            'type'      => 'province',
            'name'      => 'Test Province',
        ]);
        $this->city = Region::create([
            'code'      => 'CITY-TEST-' . uniqid(),
            'parent_id' => $this->province->id,
            'type'      => 'city',
            'name'      => 'Test City',
        ]);

        // Create several Orders via factory, with province_id and city_id filled
        $this->orders = Order::factory()
            ->count(3)
            ->create([
                'province_id' => $this->province->id,
                'city_id'     => $this->city->id,
            ])
            ->all();
    }

    // -------------------------------------------------------------------------
    // Requirement 15.1 — Admin with 'view-orders' can view orders list (HTTP 200)
    // -------------------------------------------------------------------------

    #[Test]
    public function it_admin_can_view_orders_with_permission(): void
    {
        $response = $this->actingAs($this->viewOrdersUser)
            ->get('/orders');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Requirement 15.2 — User without 'view-orders' gets HTTP 403
    // -------------------------------------------------------------------------

    #[Test]
    public function it_unauthorized_user_cannot_view_orders(): void
    {
        $response = $this->actingAs($this->noPermissionUser)
            ->get('/orders');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Requirement 15.3 — Admin with 'manage-orders' can update order status
    //                     → redirect (302) or HTTP 200
    // -------------------------------------------------------------------------

    #[Test]
    public function it_admin_can_update_order_status(): void
    {
        $order = $this->orders[0];

        $response = $this->actingAs($this->manageOrdersUser)
            ->patch("/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'confirmed',
        ]);
    }

    // -------------------------------------------------------------------------
    // Requirement 15.4 — Admin with 'manage-orders' can delete an order
    //                     → redirect, order is soft-deleted
    // -------------------------------------------------------------------------

    #[Test]
    public function it_admin_can_delete_order_with_permission(): void
    {
        $order = $this->orders[1];

        $response = $this->actingAs($this->manageOrdersUser)
            ->delete("/orders/{$order->id}");

        $response->assertRedirect();

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    // -------------------------------------------------------------------------
    // Requirement 15.5 — User without 'manage-orders' gets HTTP 403 on delete
    // -------------------------------------------------------------------------

    #[Test]
    public function it_delete_order_without_permission_returns_403(): void
    {
        $order = $this->orders[2];

        // viewOrdersUser has 'view-orders' but NOT 'manage-orders'
        $response = $this->actingAs($this->viewOrdersUser)
            ->delete("/orders/{$order->id}");

        $response->assertStatus(403);
    }
}
