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

class AuthController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function createAdminLogin()
    {
        return view('auth.admin-login');
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
            // User doesn't have admin role, logout and redirect
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke admin dashboard.');
        }

        // HTMX Redirect
        if ($request->header('HX-Request')) {
            return response()->make('', 200, ['HX-Redirect' => route('dashboard')]);
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

        if ($request->header('HX-Request')) {
            return response()->make('', 200, ['HX-Redirect' => route('login')]);
        }

        return redirect(route('login'));
    }

    /**
     * Display the registration view.
     */
    public function showRegistration(string $type)
    {
        if (!in_array($type, ['customer', 'partner'])) {
            abort(404);
        }

        return view('auth.register', compact('type'));
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

        // HTMX Redirect
        if ($request->header('HX-Request')) {
            return response()->make('', 200, ['HX-Redirect' => route($redirectRoute)]);
        }

        return redirect()->route($redirectRoute)
            ->with('success', 'Selamat datang di Rima Craft! Akun Anda berhasil dibuat.');
    }
}
