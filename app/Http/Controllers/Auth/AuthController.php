<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Public unified login (customer + reseller)
    // -------------------------------------------------------------------------

    /**
     * Display the unified public login page (all non-admin roles).
     */
    public function createLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return Inertia::render('Auth/Login', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'type'         => 'public',
        ]);
    }

    /**
     * Handle unified public login — auto-detects role and redirects.
     */
    public function storeLogin(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Honor explicit ?redirect= param (only internal URLs allowed)
        $redirect = $request->input('redirect');
        if ($redirect && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
            return redirect($redirect);
        }

        return $this->redirectByRole(auth()->user());
    }

    // -------------------------------------------------------------------------
    // Admin login (separate page, /admin/login)
    // -------------------------------------------------------------------------

    /**
     * Display the admin login view.
     */
    public function createAdminLogin(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'type'         => 'admin',
        ]);
    }

    /**
     * Handle admin login.
     */
    public function storeAdminLogin(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->redirectByRole(auth()->user());
    }

    // -------------------------------------------------------------------------
    // Register (single unified page)
    // -------------------------------------------------------------------------

    /**
     * Display the registration page.
     * Accepts optional ?type=reseller query param to pre-switch to reseller form.
     */
    public function showRegistration(string $type = 'customer'): InertiaResponse
    {
        // Allow override via query string (?type=reseller)
        $type = request()->query('type', $type);

        if (!in_array($type, ['customer', 'reseller'])) {
            $type = 'customer';
        }

        return Inertia::render('Auth/Register', [
            'type'         => $type,
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'provinces'    => \App\Models\Region::where('type', 'province')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray(),
        ]);
    }

    /**
     * Handle registration (type comes from form field, not URL).
     */
    public function register(RegistrationRequest $request, string $type = 'customer')
    {
        // Prefer type from POST body if provided
        $type = $request->input('register_type', $type);

        if (!in_array($type, ['customer', 'reseller'])) {
            $type = 'customer';
        }

        $userData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ];

        if ($type === 'reseller') {
            $userData['reseller_status'] = 'pending';
        }

        $user = User::create($userData);

        $role = Role::where('name', $type)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Contact::create([
            'user_id'       => $user->id,
            'type'          => $type,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'province_id'   => $request->province_id,
            'city_id'       => $request->city_id,
            'company_name'  => $request->company_name,
            'business_type' => $request->business_type,
        ]);

        $user->unsetRelation('roles');
        Auth::login($user);
        $request->session()->regenerate();

        if ($type === 'reseller') {
            // Pending reseller → go to customer dashboard with a notice
            return redirect()->route('customer.dashboard')
                ->with('info', 'Akun reseller Anda berhasil dibuat! Kami akan memverifikasi pendaftaran Anda dalam 1-2 hari kerja.');
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Selamat datang di Rima Craft! Akun Anda berhasil dibuat.');
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Redirect user to the correct portal based on their primary role.
     * Resellers with pending status are treated like customers.
     */
    private function redirectByRole(User $user)
    {
        // Force fresh role data — prevent stale Eloquent cache
        $user->unsetRelation('roles');
        $user->refresh();

        $adminRoles = ['dev-admin', 'super-admin', 'owner', 'operator'];

        // Admin check has highest priority — direct redirect, ignore any stored intended URL
        if ($user->roles()->whereIn('name', $adminRoles)->exists()) {
            session()->forget('url.intended');
            return redirect()->route('dashboard');
        }
        // Verified reseller → reseller dashboard
        if ($user->isReseller() && $user->isVerifiedReseller()) {
            return redirect()->route('reseller.dashboard')
                ->with('success', 'Selamat datang kembali!');
        }

        // Reseller pending or rejected → customer-mode with notice
        if ($user->isReseller()) {
            $message = $user->reseller_status === 'rejected'
                ? 'Pendaftaran reseller Anda ditolak. Hubungi kami untuk informasi lebih lanjut.'
                : 'Akun reseller Anda sedang dalam proses verifikasi.';

            return redirect()->route('customer.dashboard')
                ->with('info', $message);
        }

        // Regular customer
        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard')
                ->with('success', 'Selamat datang kembali!');
        }

        // No recognisable role — boot them out
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Akun Anda tidak memiliki akses.');
    }
}
