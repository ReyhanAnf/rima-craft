<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

/**
 * Feature tests for authentication flows.
 *
 * Covers: admin login, failed login, logout, customer registration,
 * duplicate email, and customer role redirect on admin login.
 *
 * Validates: Requirements 12.1, 12.2, 12.3, 12.4, 12.5, 12.6
 */
class AuthenticationTest extends TestCase
{
    use DatabaseTransactions, CreatesTestData;

    private User $adminUser;
    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the 'customer' role exists so createUserWithRole('customer') can find it.
        Role::firstOrCreate(['name' => 'customer']);

        $this->adminUser   = $this->createAdminUser();
        $this->customerUser = $this->createUserWithRole('customer');
    }

    // -------------------------------------------------------------------------
    // Requirement 12.1 — Admin login with valid credentials → redirect /dashboard
    // -------------------------------------------------------------------------

    #[Test]
    public function it_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => $this->adminUser->email,
            'password' => 'password', // default UserFactory password
        ]);

        $response->assertRedirectContains('dashboard');
    }

    // -------------------------------------------------------------------------
    // Requirement 12.2 — Wrong password → redirect back with session error on 'email'
    // -------------------------------------------------------------------------

    #[Test]
    public function it_login_fails_with_wrong_password(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => $this->adminUser->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    // -------------------------------------------------------------------------
    // Requirement 12.3 — Authenticated user can logout → redirect to /login
    // -------------------------------------------------------------------------

    #[Test]
    public function it_authenticated_user_can_logout(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/logout');

        // route('login') redirects to /customer/login; either URL is acceptable.
        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location', '');
        $this->assertTrue(
            str_contains($redirectUrl, 'login'),
            "Expected redirect URL to contain 'login', got: {$redirectUrl}"
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 12.4 — Customer registers with valid data →
    //                     User + Contact created, redirect customer dashboard
    // -------------------------------------------------------------------------

    #[Test]
    public function it_customer_can_register_with_valid_data(): void
    {
        $email = 'newcustomer@example.com';

        $response = $this->post('/customer/register', [
            'name'                  => 'New Customer',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'phone'                 => '081234567890',
            'agree_terms'           => '1',
        ]);

        // User must be created
        $this->assertDatabaseHas('users', ['email' => $email]);

        // Contact of type 'customer' must be created for that user
        $user = User::where('email', $email)->firstOrFail();
        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'type'    => 'customer',
        ]);

        // Should redirect to customer dashboard area
        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location', '');
        $this->assertTrue(
            str_contains($redirectUrl, 'customer'),
            "Expected redirect URL to contain 'customer', got: {$redirectUrl}"
        );
    }

    // -------------------------------------------------------------------------
    // Requirement 12.5 — Duplicate email on register → errors on 'email'
    // -------------------------------------------------------------------------

    #[Test]
    public function it_register_fails_with_duplicate_email(): void
    {
        $email = 'duplicate@example.com';

        // Pre-create a user with this email so the database already has it.
        User::factory()->create(['email' => $email]);

        // Now try to register with the same email as a guest.
        // No prior login session exists, so the guest middleware won't block us.
        $response = $this->post('/customer/register', [
            'name'                  => 'Second User',
            'email'                 => $email,
            'password'              => 'password456',
            'password_confirmation' => 'password456',
            'phone'                 => '089876543210',
            'agree_terms'           => '1',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // -------------------------------------------------------------------------
    // Requirement 12.6 — Customer credentials on /admin/login →
    //                     redirect to customer portal (NOT /dashboard)
    // -------------------------------------------------------------------------

    #[Test]
    public function it_customer_user_is_redirected_to_customer_portal_on_admin_login(): void
    {
        // POST /admin/login is behind `guest` middleware, so we must send
        // form credentials rather than using actingAs().
        $response = $this->post('/admin/login', [
            'email'    => $this->customerUser->email,
            'password' => 'password', // default UserFactory password
        ]);

        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location', '');

        // Must redirect to the customer portal (/customer/dashboard),
        // NOT to the bare admin dashboard (/dashboard or the route named 'dashboard').
        $adminDashboardUrl = route('dashboard'); // resolves to http://localhost/dashboard
        $this->assertNotEquals(
            $adminDashboardUrl,
            $redirectUrl,
            "Customer should NOT be redirected to admin /dashboard"
        );
        $this->assertStringContainsString(
            'customer',
            $redirectUrl,
            "Customer should be redirected to the customer portal, got: {$redirectUrl}"
        );
    }
}
