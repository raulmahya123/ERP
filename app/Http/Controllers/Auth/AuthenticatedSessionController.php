<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('gm')) {
            return redirect()->intended(route('gm.dashboard'));
        } elseif ($user->hasRole('manager')) {
            return redirect()->intended(route('manager.dashboard'));
        } elseif ($user->hasRole('foreman')) {
            return redirect()->intended(route('foreman.dashboard'));
        } elseif ($user->hasRole('operator')) {
            return redirect()->intended(route('operator.dashboard'));
        } elseif ($user->hasRole('hse_officer')) {
            return redirect()->intended(route('hse.dashboard'));
        } elseif ($user->hasRole('hr')) {
            return redirect()->intended(route('hr.dashboard'));
        } elseif ($user->hasRole('finance')) {
            return redirect()->intended(route('finance.dashboard'));
        }

        // fallback kalau user tidak punya role
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
