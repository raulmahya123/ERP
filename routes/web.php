<?php

use Illuminate\Support\Facades\Route;

// Controllers (Pages & Auth)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\RoleDashboardController;

// Admin Controllers
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\SiteContextController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\Admin\SiteController; // CRUD daftar site

// Master Data Controller (generic handler per-entity)
use App\Http\Controllers\MasterDataController;

/*
|--------------------------------------------------------------------------
| Route Patterns
|--------------------------------------------------------------------------
*/
Route::pattern('record', '[0-9a-fA-F-]{36}');
Route::pattern('entity', '(units|pits|stockpiles|cost_centers|accounts|employees|asset_categories)');

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login')->name('root');

/*
|--------------------------------------------------------------------------
| Dashboard + Profile (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [StaticPageController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Pilih Site (dipanggil oleh middleware EnsureSiteSelected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->get('/sites/select', function () {
        $sites = \App\Models\Site::orderBy('name')->get();
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Pilih site terlebih dahulu.',
                'sites'   => $sites->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            ]);
        }
        return view('admin.sites.select', compact('sites'));
    })
    ->name('sites.select');

/*
|--------------------------------------------------------------------------
| Admin Area (GM & Manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm|manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::get('users-export', [UserController::class, 'export'])->name('users.export');
        Route::resource('divisions', DivisionController::class);
    });

/*
|--------------------------------------------------------------------------
| Master Data (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/master')
    ->as('admin.master.')
    ->group(function () {
        // Permissions per record
        Route::get('{entity}/{record}/permissions', [MasterDataController::class, 'permissions'])
            ->whereUuid('record')->name('permissions');
        Route::post('{entity}/{record}/permissions', [MasterDataController::class, 'permissionsUpdate'])
            ->whereUuid('record')->name('permissions.update');

        // Utilities
        Route::get('{entity}/lookup', [MasterDataController::class, 'lookup'])->name('lookup');
        Route::get('{entity}/export', [MasterDataController::class, 'export'])->name('export');
        Route::post('{entity}/import', [MasterDataController::class, 'import'])->name('import');
        Route::get('{entity}/import-template', [MasterDataController::class, 'importTemplate'])->name('import.template');
        Route::delete('{entity}/bulk-delete', [MasterDataController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('{entity}/{record}/duplicate', [MasterDataController::class, 'duplicate'])
            ->whereUuid('record')->name('duplicate');

        // CRUD utama
        Route::get('{entity}', [MasterDataController::class, 'index'])->name('index');
        Route::get('{entity}/create', [MasterDataController::class, 'create'])->name('create');
        Route::post('{entity}', [MasterDataController::class, 'store'])->name('store');
        Route::get('{entity}/{record}', [MasterDataController::class, 'show'])
            ->whereUuid('record')->name('show');
        Route::get('{entity}/{record}/edit', [MasterDataController::class, 'edit'])
            ->whereUuid('record')->name('edit');
        Route::put('{entity}/{record}', [MasterDataController::class, 'update'])
            ->whereUuid('record')->name('update');
        Route::delete('{entity}/{record}', [MasterDataController::class, 'destroy'])
            ->whereUuid('record')->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Kelola Akses User (GM only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/access')
    ->as('admin.access.')
    ->group(function () {
        Route::get('users', [UserAccessController::class, 'index'])->name('users.index');
        Route::get('users/{user}/role', [UserAccessController::class, 'editRole'])->name('users.role.edit');
        Route::post('users/{user}/role', [UserAccessController::class, 'updateRole'])->name('users.role');
    });

/*
|--------------------------------------------------------------------------
| Dashboards per Role
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'hasrole:gm'])
    ->get('/gm', [RoleDashboardController::class, 'gm'])->name('gm.dashboard');

Route::middleware(['auth', 'hasrole:manager'])
    ->get('/manager', [RoleDashboardController::class, 'manager'])->name('manager.dashboard');

Route::middleware(['auth', 'hasrole:foreman'])
    ->get('/foreman', [RoleDashboardController::class, 'foreman'])->name('foreman.dashboard');

Route::middleware(['auth', 'hasrole:operator'])
    ->get('/operator', [RoleDashboardController::class, 'operator'])->name('operator.dashboard');

Route::middleware(['auth', 'hasrole:hse_officer'])
    ->get('/hse', [RoleDashboardController::class, 'hse'])->name('hse.dashboard');

Route::middleware(['auth', 'hasrole:hr'])
    ->get('/hr', [RoleDashboardController::class, 'hr'])->name('hr.dashboard');

Route::middleware(['auth', 'hasrole:finance'])
    ->get('/finance', [RoleDashboardController::class, 'finance'])->name('finance.dashboard');

/*
|--------------------------------------------------------------------------
| GM: Site Switcher & Site Config
|--------------------------------------------------------------------------
*/
// Ganti konteks site aktif
Route::middleware(['auth', 'hasrole:gm'])
    ->post('/admin/site/switch', [SiteContextController::class, 'switch'])
    ->name('admin.site.switch');

// Konfigurasi per-site (butuh site.selected)
Route::middleware(['auth', 'hasrole:gm', 'site.selected'])
    ->group(function () {
        Route::get('/admin/site-config',  [SiteConfigController::class, 'edit'])
            ->name('admin.site_config.edit');
        Route::post('/admin/site-config', [SiteConfigController::class, 'update'])
            ->name('admin.site_config.update');
    });

// CRUD daftar site
Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/sites')
    ->as('admin.sites.')
    ->group(function () {
        Route::get('/',            [SiteController::class, 'index'])->name('index');
        Route::get('/create',      [SiteController::class, 'create'])->name('create');
        Route::post('/',           [SiteController::class, 'store'])->name('store');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}',      [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}',   [SiteController::class, 'destroy'])->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
