<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        // tampilkan form dengan token (email di query)
        return view('auth.reset-password', ['token' => $token]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // langsung login habis reset
                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('dashboard')->with('status', __($status))
            : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
    }
}
