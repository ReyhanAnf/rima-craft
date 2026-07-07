<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot-password form.
     */
    public function show()
    {
        return Inertia::render('Auth/ForgotPassword', [
            'businessName' => config('settings.business_name', 'Rima Craft'),
        ]);
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link reset password telah dikirim ke email Anda. Periksa kotak masuk Anda.');
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }
}
