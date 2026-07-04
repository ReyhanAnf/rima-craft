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
    /**
     * Display the admin login view.
     */
    public function createAdminLogin(): InertiaResponse
    {
        return Inertia::render('Auth/Login', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'type' => 'admin',
        ]);
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function storeAdminLogin(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user has admin access (super-admin, owner, operator)
        $user = auth()->user();
        $adminRoles = ['super-admin', 'owner', 'operator'];
        $hasAdminRole = $user->roles()->whereIn('name', $adminRoles)->exists();

        if (!$hasAdminRole) {
            // Check if user is a customer or reseller
            $isCustomer = $user->roles()->where('name', 'customer')->exists();
            $isReseller = $user->roles()->where('name', 'reseller')->exists();

            if ($isCustomer) {
                return redirect()->route('customer.dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            if ($isReseller) {
                return redirect()->route('reseller.dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            // User doesn't have access, logout and redirect
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke dashboard.');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Display the customer login view.
     */
    public function createCustomerLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->roles()->where('name', 'reseller')->exists()) {
                return redirect()->route('reseller.dashboard');
            }
            if ($user->roles()->where('name', 'customer')->exists()) {
                return redirect()->route('customer.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'type' => 'customer',
        ]);
    }

    /**
     * Handle customer login.
     */
    public function storeCustomerLogin(LoginRequest $request)
    {
        return $this->storeAdminLogin($request);
    }

    /**
     * Display the reseller login view.
     */
    public function createResellerLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->roles()->where('name', 'reseller')->exists()) {
                return redirect()->route('reseller.dashboard');
            }
            if ($user->roles()->where('name', 'customer')->exists()) {
                return redirect()->route('customer.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'type' => 'reseller',
        ]);
    }

    /**
     * Handle reseller login.
     */
    public function storeResellerLogin(LoginRequest $request)
    {
        return $this->storeAdminLogin($request);
    }

    /**
     * Display customer registration view.
     */
    public function showCustomerRegistration(): InertiaResponse
    {
        return $this->showRegistration('customer');
    }

    /**
     * Handle customer registration.
     */
    public function registerCustomer(RegistrationRequest $request)
    {
        return $this->register($request, 'customer');
    }

    /**
     * Display reseller registration view.
     */
    public function showResellerRegistration(): InertiaResponse
    {
        return $this->showRegistration('reseller');
    }

    /**
     * Handle reseller registration.
     */
    public function registerReseller(RegistrationRequest $request)
    {
        return $this->register($request, 'reseller');
    }

    /**
     * Display the customer/partner login view (backward compatibility).
     */
    public function create(): InertiaResponse
    {
        return $this->createCustomerLogin();
    }

    /**
     * Handle an incoming authentication request (backward compatibility).
     */
    public function store(LoginRequest $request)
    {
        return $this->storeAdminLogin($request);
    }

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

    /**
     * Display the registration view.
     */
    public function showRegistration(string $type): InertiaResponse
    {
        if (!in_array($type, ['customer', 'reseller'])) {
            abort(404);
        }

        return Inertia::render('Auth/Register', [
            'type' => $type,
            'businessName' => config('settings.business_name', 'Rima Craft'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(RegistrationRequest $request, string $type)
    {
        if (!in_array($type, ['customer', 'reseller'])) {
            abort(404);
        }

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign role
        $roleName = $type === 'reseller' ? 'reseller' : 'customer';
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Auto-create Contact
        $contactType = $type === 'reseller' ? 'reseller' : 'customer';
        Contact::create([
            'user_id' => $user->id,
            'type' => $contactType,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'business_type' => $request->business_type,
        ]);

        // Unset roles relation cache before logging in to ensure the session contains the assigned role.
        $user->unsetRelation('roles');

        // Login the user
        Auth::login($user);

        // Regenerate session
        $request->session()->regenerate();

        // Redirect to appropriate portal
        $redirectRoute = $type === 'reseller' ? 'reseller.dashboard' : 'customer.dashboard';

        return redirect()->route($redirectRoute)
            ->with('success', 'Selamat datang di Rima Craft! Akun Anda berhasil dibuat.');
    }
}
