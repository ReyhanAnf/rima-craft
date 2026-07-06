<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
     * - If account exists: link Google ID and login.
     * - If account is new: create Customer account and login.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        // Find by google_id first (already linked)
        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // Find by email (account exists but Google not yet linked)
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link Google to existing account
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            } else {
                // Create new Customer account
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'password'  => null, // no password — Google-only account
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);

                // Assign customer role
                $customerRole = Role::where('name', 'customer')->first();
                if ($customerRole) {
                    $user->roles()->attach($customerRole->id);
                }

                // Auto-create contact
                Contact::create([
                    'user_id' => $user->id,
                    'type'    => 'customer',
                    'name'    => $googleUser->getName(),
                    'email'   => $googleUser->getEmail(),
                ]);
            }
        } else {
            // Update avatar in case it changed
            $user->update(['avatar' => $googleUser->getAvatar()]);
        }

        $user->unsetRelation('roles');
        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Redirect to the correct portal based on role.
     */
    private function redirectByRole(User $user)
    {
        $adminRoles = ['dev-admin', 'super-admin', 'owner', 'operator'];

        if ($user->roles()->whereIn('name', $adminRoles)->exists()) {
            return redirect()->intended(route('dashboard'));
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
