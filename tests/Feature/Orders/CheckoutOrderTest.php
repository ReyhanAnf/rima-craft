<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Models\Contact;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

/**
 * Feature tests for the public checkout / order placement flow.
 *
 * Covers guest checkout, authenticated user order, account creation on checkout,
 * reseller down-payment logic, empty-cart validation, and order number format.
 *
 * Validates: Requirements 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7
 */
class CheckoutOrderTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private PaymentMethod $paymentMethod;
    private Region $province;
    private Region $city;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the 'reseller' role exists so createUserWithRole('reseller') works.
        Role::firstOrCreate(['name' => 'reseller']);
        // Ensure the 'customer' role exists for account-creation flow.
        Role::firstOrCreate(['name' => 'customer']);

        $this->paymentMethod = PaymentMethod::create([
            'code'      => 'transfer',
            'name'      => 'Transfer Bank',
            'type'      => 'transfer',
            'is_active' => true,
        ]);

        $this->province = Region::create([
            'code' => 'PROV-TST',
            'name' => 'Provinsi Test',
            'type' => 'province',
        ]);

        $this->city = Region::create([
            'code'      => 'CITY-TST',
            'name'      => 'Kota Test',
            'type'      => 'city',
            'parent_id' => $this->province->id,
        ]);

        $this->product = Product::factory()->create([
            'base_price'     => 100_000,
            'reseller_price' => 80_000,
            'current_stock'  => 50,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper: base valid order payload
    // -------------------------------------------------------------------------

    /**
     * Return a complete, valid POST payload for POST /order/store.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validOrderPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'    => 'Budi Santoso',
            'customer_phone'   => '081234567890',
            'customer_email'   => 'budi@example.com',
            'customer_address' => 'Jl. Contoh No. 1',
            'province_id'      => $this->province->id,
            'city_id'          => $this->city->id,
            'payment_method'   => 'transfer',
            'order_method'     => 'form',
            'items'            => json_encode([['id' => $this->product->id, 'qty' => 1]]),
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Requirement 14.1 — Guest places order via form → redirect to success, status=pending
    // -------------------------------------------------------------------------

    #[Test]
    public function it_guest_can_place_order_via_form(): void
    {
        $response = $this->post('/order/store', $this->validOrderPayload());

        // Should redirect to success page
        $response->assertRedirect();
        $location = $response->headers->get('Location', '');
        $this->assertStringContainsString('success', $location, "Expected redirect to order success page, got: {$location}");

        // Order must be created with status=pending
        $this->assertDatabaseHas('orders', [
            'customer_email' => 'budi@example.com',
            'status'         => 'pending',
        ]);
    }

    // -------------------------------------------------------------------------
    // Requirement 14.2 — Logged-in user's order is linked to their user_id
    // -------------------------------------------------------------------------

    #[Test]
    public function it_logged_in_user_order_is_linked_to_user_id(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->actingAs($user)
            ->post('/order/store', $this->validOrderPayload([
                'customer_email' => $user->email,
            ]));

        $response->assertRedirect();
        $location = $response->headers->get('Location', '');
        $this->assertStringContainsString('success', $location);

        $order = Order::where('customer_email', $user->email)->latest()->first();
        $this->assertNotNull($order, 'Order should have been created.');
        $this->assertEquals($user->id, $order->user_id, 'Order user_id should match the authenticated user.');
    }

    // -------------------------------------------------------------------------
    // Requirement 14.3 — Guest with create_account=true → User+Contact created, logged in
    // -------------------------------------------------------------------------

    #[Test]
    public function it_creates_account_and_logs_in_when_create_account_is_true(): void
    {
        $email = 'newuser_checkout@example.com';

        $response = $this->post('/order/store', $this->validOrderPayload([
            'customer_email'        => $email,
            'create_account'        => '1',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]));

        $response->assertRedirect();

        // User must be created
        $this->assertDatabaseHas('users', ['email' => $email]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'User should have been created during checkout.');

        // Contact must be created for that user
        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'type'    => 'customer',
        ]);

        // User should be logged in after the request (session established)
        $this->assertAuthenticatedAs($user);

        // Order must reference the newly created user
        $this->assertDatabaseHas('orders', [
            'customer_email' => $email,
            'user_id'        => $user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Requirement 14.4 — Reseller with DP >= 30% → payment_status=partial
    // -------------------------------------------------------------------------

    #[Test]
    public function it_reseller_with_dp_30_percent_gets_partial_payment_status(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        // Product reseller_price = 80_000, no shipping → total = 80_000
        // 30% of 80_000 = 24_000, so dp=24_000 is exactly the minimum.
        $dp = 24_000;

        $response = $this->actingAs($reseller)
            ->post('/order/store', $this->validOrderPayload([
                'customer_email'      => $reseller->email,
                'payment_mode'        => 'dp',
                'down_payment_amount' => (string) $dp,
            ]));

        $response->assertRedirect();
        $location = $response->headers->get('Location', '');
        $this->assertStringContainsString('success', $location);

        $order = Order::where('customer_email', $reseller->email)->latest()->first();
        $this->assertNotNull($order, 'Order should have been created.');
        $this->assertEquals('partial', $order->payment_status, 'payment_status should be partial when DP >= 30%.');

        // remaining_balance must be total - dp (i.e. 80_000 - 24_000 = 56_000)
        $expectedRemaining = (float) $order->total - $dp;
        $this->assertEquals(
            $expectedRemaining,
            (float) $order->remaining_balance,
            "remaining_balance should equal total - down_payment_amount."
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 14.5 — Reseller with negative DP → validation error on down_payment_amount
    // -------------------------------------------------------------------------

    #[Test]
    public function it_reseller_with_negative_dp_gets_validation_error(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        $dp = -100;

        $response = $this->actingAs($reseller)
            ->post('/order/store', $this->validOrderPayload([
                'customer_email'      => $reseller->email,
                'payment_mode'        => 'dp',
                'down_payment_amount' => (string) $dp,
            ]));

        $response->assertSessionHasErrors('down_payment_amount');
    }

    #[Test]
    public function it_reseller_can_pay_zero_dp(): void
    {
        $reseller = $this->createUserWithRole('reseller');

        $dp = 0;

        $response = $this->actingAs($reseller)
            ->post('/order/store', $this->validOrderPayload([
                'customer_email'      => $reseller->email,
                'payment_mode'        => 'dp',
                'down_payment_amount' => (string) $dp,
            ]));

        $response->assertRedirect();
        $location = $response->headers->get('Location', '');
        $this->assertStringContainsString('success', $location);

        $order = Order::where('customer_email', $reseller->email)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(0, (float) $order->down_payment_amount);
    }

    // -------------------------------------------------------------------------
    // Requirement 14.6 — Empty items → error containing "Keranjang belanja kosong."
    // -------------------------------------------------------------------------

    #[Test]
    public function it_empty_items_returns_error(): void
    {
        // Send empty items array encoded as JSON string
        $response = $this->post('/order/store', $this->validOrderPayload([
            'items' => json_encode([]),
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('items');

        $errors = session('errors');
        $this->assertNotNull($errors, 'Session should contain errors bag.');

        $itemErrors = $errors->get('items');
        $this->assertNotEmpty($itemErrors, 'Should have error(s) for items field.');

        $found = false;
        foreach ($itemErrors as $msg) {
            if (str_contains($msg, 'Keranjang belanja kosong.')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Expected error message 'Keranjang belanja kosong.' in items errors.");
    }

    // -------------------------------------------------------------------------
    // Requirement 14.7 — Successful order has order_number matching ORD-YYYYMMDD-NNN
    // -------------------------------------------------------------------------

    #[Test]
    public function it_order_number_matches_expected_format(): void
    {
        $response = $this->post('/order/store', $this->validOrderPayload([
            'customer_email' => 'format_test@example.com',
        ]));

        $response->assertRedirect();

        $order = Order::where('customer_email', 'format_test@example.com')->latest()->first();
        $this->assertNotNull($order, 'Order should have been created.');
        $this->assertMatchesRegularExpression(
            '/^ORD-\d{8}-\d{3}$/',
            $order->order_number,
            "order_number '{$order->order_number}' does not match expected format ORD-YYYYMMDD-NNN."
        );
    }
}
