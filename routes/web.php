<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root → langsung ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard umum (semua role)
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// ----------------------
// Profile (default Breeze)
// ----------------------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =====================
// ADMIN AREA — Hanya GM & Manager
// NOTE: Spatie pakai delimiter OR = "|" (pipe), bukan koma.
// =====================
Route::middleware(['auth', 'role:gm|manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class);
    });

// =====================
// DASHBOARD PER ROLE
// =====================

// GM khusus
Route::middleware(['auth', 'role:gm'])
    ->get('/gm', fn () => view('roles.gm'))
    ->name('gm.dashboard');

// Manager khusus
Route::middleware(['auth', 'role:manager'])
    ->get('/manager', fn () => view('roles.manager'))
    ->name('manager.dashboard');

// Foreman khusus
Route::middleware(['auth', 'role:foreman'])
    ->get('/foreman', fn () => view('roles.foreman'))
    ->name('foreman.dashboard');

// Operator khusus
Route::middleware(['auth', 'role:operator'])
    ->get('/operator', fn () => view('roles.operator'))
    ->name('operator.dashboard');

// HSE Officer khusus
Route::middleware(['auth', 'role:hse_officer'])
    ->get('/hse', fn () => view('roles.hse'))
    ->name('hse.dashboard');

// HR khusus
Route::middleware(['auth', 'role:hr'])
    ->get('/hr', fn () => view('roles.hr'))
    ->name('hr.dashboard');

// Finance khusus
Route::middleware(['auth', 'role:finance'])
    ->get('/finance', fn () => view('roles.finance'))
    ->name('finance.dashboard');

// Auth routes (Breeze/Fortify/etc.)
require __DIR__ . '/auth.php';
