<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use Illuminate\Support\Facades\Route;

// Root
Route::get('/', fn () => redirect()->route('login'));

// Dashboard umum
Route::middleware('auth')->get('/dashboard', fn () => view('dashboard'))->name('dashboard');

// Profile (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =====================
// ADMIN AREA — Manager saja di route, tapi GM ikut lolos via bypass
// =====================
Route::middleware(['auth', 'hasrole:manager'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::resource('divisions', DivisionController::class); // tambahan
    });

// =====================
// DASHBOARD PER ROLE
// =====================

// GM only
Route::middleware(['auth', 'hasrole:gm'])
    ->get('/gm', fn () => view('roles.gm'))
    ->name('gm.dashboard');

// Yang lain: cukup role utamanya,
// GM tetap bisa akses karena bypass di middleware
Route::middleware(['auth', 'hasrole:manager'])
    ->get('/manager', fn () => view('roles.manager'))
    ->name('manager.dashboard');

Route::middleware(['auth', 'hasrole:foreman'])
    ->get('/foreman', fn () => view('roles.foreman'))
    ->name('foreman.dashboard');

Route::middleware(['auth', 'hasrole:operator'])
    ->get('/operator', fn () => view('roles.operator'))
    ->name('operator.dashboard');

Route::middleware(['auth', 'hasrole:hse_officer'])
    ->get('/hse', fn () => view('roles.hse'))
    ->name('hse.dashboard');

Route::middleware(['auth', 'hasrole:hr'])
    ->get('/hr', fn () => view('roles.hr'))
    ->name('hr.dashboard');

Route::middleware(['auth', 'hasrole:finance'])
    ->get('/finance', fn () => view('roles.finance'))
    ->name('finance.dashboard');

// Auth routes (Breeze/Fortify/etc.)
require __DIR__ . '/auth.php';
