<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    /**
     * Show the reset-password form (linked from email).
     */
    public function show(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
            'token'        => $token,
            'email'        => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the reset-password form submission.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
        ], [
            'email.required'             => 'Alamat email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'password.required'          => 'Password wajib diisi.',
            'password.min'               => 'Password minimal 8 karakter.',
            'password.confirmed'         => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan masuk dengan password baru Anda.');
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}
