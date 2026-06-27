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
            // Check if user is a customer or partner
            $isCustomer = $user->roles()->where('name', 'customer')->exists();
            $isPartner = $user->roles()->where('name', 'partner')->exists();

            if ($isCustomer) {
                return redirect()->route('customer.dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            if ($isPartner) {
                return redirect()->route('partner.dashboard')
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
     * Display the login view (backward compatibility - redirect to admin).
     */
    public function create()
    {
        return redirect()->route('admin.login');
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
        if (!in_array($type, ['customer', 'partner'])) {
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
        if (!in_array($type, ['customer', 'partner'])) {
            abort(404);
        }

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign role
        $roleName = $type === 'partner' ? 'partner' : 'customer';
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Auto-create Contact
        $contactType = $type === 'partner' ? 'partner' : 'customer';
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

        // Login the user
        Auth::login($user);

        // Regenerate session
        $request->session()->regenerate();

        // Redirect to appropriate portal
        $redirectRoute = $type === 'partner' ? 'partner.dashboard' : 'customer.dashboard';

        return redirect()->route($redirectRoute)
            ->with('success', 'Selamat datang di Rima Craft! Akun Anda berhasil dibuat.');
    }
}
