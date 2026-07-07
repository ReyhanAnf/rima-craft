<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Models\Contact;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     *
     * Cases:
     * A) Account already exists (by google_id or email) → link & login directly.
     * B) Brand-new Google user → store Google data in session → redirect to
     *    the registration completion page so the user can fill in missing fields.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        // Case A1: already linked
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Case A2: existing email, link Google
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            }
        } else {
            $user->update(['avatar' => $googleUser->getAvatar()]);
        }

        // Case A — existing user: login directly
        if ($user) {
            $user->unsetRelation('roles');
            $user->refresh();
            Auth::login($user, remember: true);
            request()->session()->regenerate();
            return $this->redirectByRole($user);
        }

        // Case B — new user: store Google data in session, redirect to complete registration
        session([
            'google_pending' => [
                'google_id' => $googleUser->getId(),
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'avatar'    => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('auth.google.complete');
    }

    /**
     * Show the registration completion form for new Google users.
     * Pre-fills name and email from the Google session data.
     */
    public function showComplete()
    {
        $pending = session('google_pending');

        if (!$pending) {
            return redirect()->route('login')
                ->with('error', 'Sesi Google tidak ditemukan. Silakan coba lagi.');
        }

        return Inertia::render('Auth/Register', [
            'type'            => 'customer',
            'businessName'    => config('settings.business_name', 'Rima Craft'),
            'provinces'       => Region::where('type', 'province')->orderBy('name')->get(['id', 'name'])->toArray(),
            'googlePending'   => [
                'name'  => $pending['name'],
                'email' => $pending['email'],
            ],
        ]);
    }

    /**
     * Handle registration form submission for new Google users.
     */
    public function storeComplete(Request $request)
    {
        $pending = session('google_pending');

        if (!$pending) {
            return redirect()->route('login')
                ->with('error', 'Sesi Google tidak ditemukan. Silakan coba lagi.');
        }

        $type = $request->input('register_type', 'customer');
        if (!in_array($type, ['customer', 'reseller'])) {
            $type = 'customer';
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string|max:1000',
            'province_id'  => 'nullable|exists:regions,id',
            'city_id'      => 'nullable|exists:regions,id',
            'company_name' => $type === 'reseller' ? 'required|string|max:255' : 'nullable|string|max:255',
            'business_type'=> 'nullable|string|max:100',
            'agree_terms'  => 'required|accepted',
        ], [
            'name.required'         => 'Nama lengkap wajib diisi',
            'company_name.required' => 'Nama perusahaan wajib diisi untuk reseller',
            'agree_terms.accepted'  => 'Anda harus menyetujui syarat dan ketentuan',
        ]);

        // Create user — no password (Google-only account)
        $userData = [
            'name'      => $request->name,
            'email'     => $pending['email'],
            'password'  => null,
            'google_id' => $pending['google_id'],
            'avatar'    => $pending['avatar'],
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
            'email'         => $pending['email'],
            'phone'         => $request->phone,
            'address'       => $request->address,
            'province_id'   => $request->province_id,
            'city_id'       => $request->city_id,
            'company_name'  => $request->company_name,
            'business_type' => $request->business_type,
        ]);

        // Clear pending session
        session()->forget('google_pending');

        $user->unsetRelation('roles');
        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $message = $type === 'reseller'
            ? 'Akun reseller Anda berhasil dibuat! Kami akan memverifikasi pendaftaran Anda dalam 1-2 hari kerja.'
            : 'Selamat datang di Rima Craft! Akun Anda berhasil dibuat.';

        return redirect()->route('customer.dashboard')
            ->with($type === 'reseller' ? 'info' : 'success', $message);
    }

    /**
     * Redirect user to the correct portal based on their role.
     */
    private function redirectByRole(User $user)
    {
        $adminRoles = ['dev-admin', 'super-admin', 'owner', 'operator'];

        if ($user->roles()->whereIn('name', $adminRoles)->exists()) {
            session()->forget('url.intended');
            return redirect()->route('dashboard');
        }

        if ($user->isReseller()) {
            if ($user->isVerifiedReseller()) {
                return redirect()->route('reseller.dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            $message = $user->reseller_status === 'rejected'
                ? 'Pendaftaran reseller Anda ditolak. Hubungi kami untuk informasi lebih lanjut.'
                : 'Akun reseller Anda sedang dalam proses verifikasi.';

            return redirect()->route('customer.dashboard')->with('info', $message);
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Selamat datang kembali!');
    }
}
