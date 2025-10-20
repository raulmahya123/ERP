<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan form "lupa password".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email user.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi dasar
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Throttle per email+IP (mencegah spam)
        $key = $this->throttleKey($validated['email'], $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
                ]);
        }

        try {
            // Hit attempt (berlaku 1 menit window)
            RateLimiter::hit($key, 60);

            // Kirim link reset
            $status = Password::sendResetLink(['email' => $validated['email']]);

            if ($status === Password::RESET_LINK_SENT) {
                // Pesan sukses (konsisten dengan Blade session('status'))
                return back()->with('status', 'Link reset password sudah dikirim. Silakan cek inbox/spam email Anda.');
            }

            // Gagal kirim (misal email tidak terdaftar)
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (\Throwable $e) {
            // Log detail utk debugging
            Log::error('Reset password email gagal dikirim', [
                'email' => $validated['email'],
                'ip'    => $request->ip(),
                'err'   => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Terjadi kesalahan saat mengirim email. Coba lagi sebentar lagi.']);
        }
    }

    protected function throttleKey(string $email, string $ip): string
    {
        return Str::lower($email).'|'.$ip.'|password-reset';
    }
}
