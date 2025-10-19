<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManpowerEntryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\SiteContextController;
use App\Http\Controllers\Admin\SiteConfigController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MasterEntityController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetAssignmentController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\TimesheetController;
use App\Http\Controllers\Admin\ShiftRosterController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\HrDailyEntryController;
use App\Http\Controllers\Admin\EmploymentContractController;
use App\Http\Controllers\Admin\CrewAssignmentController;
use App\Http\Controllers\AttendanceController as AttendanceTapController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PayroalController;
use App\Http\Controllers\PayroalProfileController;
use App\Http\Controllers\Admin\Hse\IncidentController as HseIncidentController;
use App\Http\Controllers\Admin\Hse\IncidentInvestigationController as HseInvestigationController;
use App\Http\Controllers\Admin\Hse\PicaController as HsePicaController;
use App\Http\Controllers\Admin\Hse\EnvironmentalSampleController as HseEnvSampleController;
use App\Http\Controllers\Admin\Hse\HazardReportController as HseHazardController;
use App\Http\Controllers\Admin\Hse\MediaAttachmentController as HseMediaController;
use App\Http\Controllers\Admin\Hse\KpiIndicatorController as HseKpiController;
use App\Http\Controllers\Admin\PayroalHistoryController; // <-- penting

use App\Models\PayroalHistory;

Route::pattern('record', '[0-9a-fA-F-]{36}');
Route::pattern('entity', '[a-z0-9_]+');

Route::redirect('/', '/login')->name('root');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/dashboard/assets', [DashboardController::class, 'quickStore'])
        ->middleware('hasrole:gm|manager')
        ->name('dashboard.assets.quick-store');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/attendance/tap',        [AttendanceTapController::class, 'tapPage'])->name('attendance.tap');
    Route::post('/attendance/check-in',  [AttendanceTapController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceTapController::class, 'checkOut'])->name('attendance.checkout');
});

Route::middleware(['auth'])
    ->get('/sites/select', function () {
        $sites = \App\Models\Site::orderBy('name')->get();
        return view('admin.sites.select', compact('sites'));
    })->name('sites.select');

Route::middleware(['auth'])
    ->post('/sites/select', function (\Illuminate\Http\Request $request) {
        $data = $request->validate(['site_id' => ['required', 'uuid', 'exists:sites,id']]);
        $request->session()->put('site_id', $data['site_id']);
        $intended = $request->session()->pull('url.intended');
        return redirect()->to($intended ?: route('dashboard'))
            ->with('success', 'Site aktif telah diubah.');
    })->name('sites.choose');

Route::middleware(['auth', 'hasrole:gm|manager'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users-export', [UserController::class, 'export'])->name('users.export');
        Route::resource('divisions', DivisionController::class);
    });

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/master-entities')->as('admin.master_entities.')
    ->group(function () {
        Route::get('/',                      [MasterEntityController::class, 'index'])->name('index');
        Route::get('/create',                [MasterEntityController::class, 'create'])->name('create');
        Route::post('/',                     [MasterEntityController::class, 'store'])->name('store');
        Route::get('/{master_entity}/edit',  [MasterEntityController::class, 'edit'])->name('edit');
        Route::put('/{master_entity}',       [MasterEntityController::class, 'update'])->name('update');
        Route::delete('/{master_entity}',    [MasterEntityController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth'])
    ->prefix('admin/master')->as('admin.master.')
    ->group(function () {
        Route::get('/', fn() => redirect()->route('admin.master.overview'))->name('home');
        Route::get('overview', [MasterDataController::class, 'overview'])->name('overview');
    });

Route::middleware(['auth', 'site.selected'])
    ->prefix('admin/master')->as('admin.master.')
    ->group(function () {
        Route::get('accounts', [MasterDataController::class, 'index'])
            ->defaults('entity', 'accounts')->name('accounts.index');

        Route::get('accounts/lookup', [MasterDataController::class, 'lookup'])
            ->defaults('entity', 'accounts')->name('accounts.lookup');

        Route::get('accounts/export', [MasterDataController::class, 'export'])
            ->defaults('entity', 'accounts')->name('accounts.export');

        Route::get('accounts/{record}', [MasterDataController::class, 'publicShow'])
            ->where('record', '[0-9a-fA-F-]{36}')
            ->name('accounts.show');
    });

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/master')->as('admin.master.')
    ->group(function () {
        Route::get('permissions', [MasterDataController::class, 'permissionsQuery'])
            ->name('permissions.q');

        Route::get('{entity}/{record}/permissions', [MasterDataController::class, 'permissions'])
            ->where(['entity' => '^[a-z0-9_]+$', 'record' => '[0-9a-fA-F-]{36}'])
            ->name('permissions');

        Route::post('{entity}/{record}/permissions', [MasterDataController::class, 'permissionsUpdate'])
            ->where(['entity' => '^[a-z0-9_]+$', 'record' => '[0-9a-fA-F-]{36}'])
            ->name('permissions.update');
    });

Route::middleware(['auth', 'site.selected', 'can:master.access,entity'])
    ->prefix('admin/master')->as('admin.master.')
    ->group(function () {
        $entityRegex = '^(?!overview$)(?!accounts$)(?!permissions$)[a-z0-9_]+';

        Route::get('{entity}/lookup', [MasterDataController::class, 'lookup'])
            ->where('entity', $entityRegex)->name('lookup');

        Route::get('{entity}/export', [MasterDataController::class, 'export'])
            ->where('entity', $entityRegex)->name('export');

        Route::post('{entity}/import', [MasterDataController::class, 'import'])
            ->where('entity', $entityRegex)->name('import');

        Route::get('{entity}/import-template', [MasterDataController::class, 'importTemplate'])
            ->where('entity', $entityRegex)->name('import.template');

        Route::delete('{entity}/bulk-delete', [MasterDataController::class, 'bulkDelete'])
            ->where('entity', $entityRegex)->name('bulk-delete');

        Route::post('{entity}/{record}/duplicate', [MasterDataController::class, 'duplicate'])
            ->where(['entity' => $entityRegex, 'record' => '[0-9a-fA-F-]{36}'])->name('duplicate');

        Route::get('{entity}', [MasterDataController::class, 'index'])
            ->where('entity', $entityRegex)->name('index');

        Route::get('{entity}/create', [MasterDataController::class, 'create'])
            ->where('entity', $entityRegex)->name('create');

        Route::post('{entity}', [MasterDataController::class, 'store'])
            ->where('entity', $entityRegex)->name('store');

        Route::get('{entity}/{record}', [MasterDataController::class, 'show'])
            ->where(['entity' => $entityRegex, 'record' => '[0-9a-fA-F-]{36}'])->name('show');

        Route::get('{entity}/{record}/edit', [MasterDataController::class, 'edit'])
            ->where(['entity' => $entityRegex, 'record' => '[0-9a-fA-F-]{36}'])->name('edit');

        Route::put('{entity}/{record}', [MasterDataController::class, 'update'])
            ->where(['entity' => $entityRegex, 'record' => '[0-9a-fA-F-]{36}'])->name('update');

        Route::delete('{entity}/{record}', [MasterDataController::class, 'destroy'])
            ->where(['entity' => $entityRegex, 'record' => '[0-9a-fA-F-]{36}'])->name('destroy');
    });

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/access')->as('admin.access.')
    ->group(function () {
        Route::get('users', [UserAccessController::class, 'index'])->name('users.index');
        Route::get('users/{user}/role', [UserAccessController::class, 'editRole'])->name('users.role.edit');
        Route::post('users/{user}/role', [UserAccessController::class, 'updateRole'])->name('users.role');
    });

Route::middleware(['auth', 'hasrole:gm'])->get('/gm', [RoleDashboardController::class, 'gm'])->name('gm.dashboard');
Route::middleware(['auth', 'hasrole:manager'])->get('/manager', [RoleDashboardController::class, 'manager'])->name('manager.dashboard');
Route::middleware(['auth', 'hasrole:foreman'])->get('/foreman', [RoleDashboardController::class, 'foreman'])->name('foreman.dashboard');
Route::middleware(['auth', 'hasrole:operator'])->get('/operator', [RoleDashboardController::class, 'operator'])->name('operator.dashboard');
Route::middleware(['auth', 'hasrole:hse_officer'])->get('/hse', [RoleDashboardController::class, 'hse'])->name('hse.dashboard');
Route::middleware(['auth', 'hasrole:hr'])->get('/hr', [RoleDashboardController::class, 'hr'])->name('hr.dashboard');
Route::middleware(['auth', 'hasrole:finance'])->get('/finance', [RoleDashboardController::class, 'finance'])->name('finance.dashboard');

Route::middleware(['auth', 'hasrole:gm'])->post('/admin/site/switch', [SiteContextController::class, 'switch'])->name('admin.site.switch');

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/sites')->as('admin.sites.')
    ->group(function () {
        Route::get('/',            [SiteController::class, 'index'])->name('index');
        Route::get('/create',      [SiteController::class, 'create'])->name('create');
        Route::post('/',           [SiteController::class, 'store'])->name('store');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}',      [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}',   [SiteController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin/audit-logs')->as('admin.audit.')
    ->group(function () {
        Route::get('/',       [AuditLogController::class, 'index'])->name('index');
        Route::get('/{log}',  [AuditLogController::class, 'show'])->whereUuid('log')->name('show');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/feed/json', [AuditLogController::class, 'feed'])->name('feed');
    });

Route::middleware(['auth', 'hasrole:gm'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::prefix('site-config')->as('site_config.')->group(function () {
            Route::get('/',                   [SiteConfigController::class, 'index'])->name('index');
            Route::get('/create',             [SiteConfigController::class, 'create'])->name('create');
            Route::post('/',                  [SiteConfigController::class, 'store'])->name('store');
            Route::get('/{site_config}/edit', [SiteConfigController::class, 'edit'])->name('edit');
            Route::put('/{site_config}',      [SiteConfigController::class, 'update'])->name('update');
            Route::delete('/{site_config}',   [SiteConfigController::class, 'destroy'])->name('destroy');
        });
    });

Route::middleware(['auth'])
    ->prefix('admin/commodities')->as('admin.commodities.')
    ->group(function () {
        Route::get('/',                 [CommodityController::class, 'index'])->name('index');
        Route::get('/create',           [CommodityController::class, 'create'])->name('create');
        Route::post('/',                [CommodityController::class, 'store'])->name('store');
        Route::get('/{commodity}/edit', [CommodityController::class, 'edit'])->name('edit');
        Route::put('/{commodity}',      [CommodityController::class, 'update'])->name('update');
        Route::delete('/{commodity}',   [CommodityController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'site.selected', 'can:viewAny,App\Models\Asset'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::resource('assets', AssetController::class)
            ->parameters(['assets' => 'asset'])
            ->whereUuid(['asset']);

        Route::prefix('assets/{asset}')->as('assets.')->whereUuid(['asset'])->group(function () {
            Route::get('assignments',        [AssetAssignmentController::class, 'index'])->name('assignments.index');
            Route::get('assignments/create', [AssetAssignmentController::class, 'create'])->name('assignments.create');
            Route::post('assignments',       [AssetAssignmentController::class, 'store'])->name('assignments.store');
        });

        Route::resource('asset-assignments', AssetAssignmentController::class)
            ->parameters(['asset-assignments' => 'assetAssignment'])
            ->except(['show']);
    });

Route::middleware(['auth', 'hasrole:gm|hr', 'site.selected'])
    ->prefix('admin')->name('admin.')->group(function () {

        Route::resource('attendance', AttendanceController::class)
            ->parameters(['attendance' => 'attendance'])->except(['show']);

        Route::resource('timesheets', TimesheetController::class)
            ->parameters(['timesheets' => 'timesheet'])->except(['show']);

        Route::resource('shift-rosters', ShiftRosterController::class)
            ->parameters(['shift-rosters' => 'shiftRoster'])->except(['show']);

        Route::resource('shifts', ShiftController::class)
            ->parameters(['shifts' => 'shift'])->except(['show']);

        Route::get('shift-rosters/shifts-by-site', [ShiftRosterController::class, 'shiftsBySite'])
            ->name('shift-rosters.shifts-by-site');

        Route::get('overtime', [TimesheetController::class, 'otIndex'])->name('overtime.index');
        Route::post('timesheets/{timesheet}/ot/submit',  [TimesheetController::class, 'otSubmit'])
            ->whereUuid('timesheet')->name('timesheets.ot.submit');
        Route::post('timesheets/{timesheet}/ot/approve', [TimesheetController::class, 'otApprove'])
            ->whereUuid('timesheet')->name('timesheets.ot.approve');
        Route::post('timesheets/{timesheet}/ot/reject',  [TimesheetController::class, 'otReject'])
            ->whereUuid('timesheet')->name('timesheets.ot.reject');

        Route::resource('locations', LocationController::class)
            ->parameters(['locations' => 'location'])->except(['show']);

        Route::prefix('hr-entries/types')->name('hr-entries.types.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/',  [HrDailyEntryController::class, 'typesIndex'])->name('index');
                Route::post('/', [HrDailyEntryController::class, 'typesStore'])->name('store');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'typesUpdate'])
                    ->name('update')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'typesDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
                Route::post('/reorder', [HrDailyEntryController::class, 'typesReorder'])->name('reorder');
            });

        Route::prefix('hr-entries/meta-form-config')->name('hr-entries.meta-form.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'metaFormConfigIndex'])->name('index');
                Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaFormConfigManage'])
                    ->name('manage')->where('type', '[A-Za-z0-9_\-]+');
                Route::get('/{type}', [HrDailyEntryController::class, 'metaFormConfigShow'])
                    ->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaFormConfigUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'metaFormConfigDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        Route::prefix('hr-entries/meta-schema')->name('hr-entries.meta-schema.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'metaSchemasIndex'])->name('index');
                Route::get('/manage/{type?}', [HrDailyEntryController::class, 'metaSchemasManage'])
                    ->name('manage')->where('type', '[A-Za-z0-9_\-]+');
                Route::get('/{type}', [HrDailyEntryController::class, 'metaSchemasShow'])
                    ->name('show')->where('type', '^(?!manage$)[A-Za-z0-9_\-]+');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'metaSchemasUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'metaSchemasDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        Route::prefix('hr-entries/approval/schemas')->name('hr-entries.approval.schemas.')
            ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
                Route::get('/', [HrDailyEntryController::class, 'approvalSchemasIndex'])->name('index');
                Route::get('/{type}', [HrDailyEntryController::class, 'approvalSchemasShow'])
                    ->name('show')->where('type', '[A-Za-z0-9_\-]+');
                Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'approvalSchemasUpsert'])
                    ->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
                Route::delete('/{type}', [HrDailyEntryController::class, 'approvalSchemasDestroy'])
                    ->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
            });

        Route::resource('hr-entries', HrDailyEntryController::class)
            ->parameters(['hr-entries' => 'entry'])->except(['show'])->whereUuid(['entry']);

        Route::post('hr-entries/{entry}/submit',  [HrDailyEntryController::class, 'approvalSubmit'])
            ->middleware('can:submit,entry')->name('hr-entries.submit')->whereUuid('entry');
        Route::post('hr-entries/{entry}/approve', [HrDailyEntryController::class, 'approvalApprove'])
            ->middleware('can:approve,entry')->name('hr-entries.approve')->whereUuid('entry');
        Route::post('hr-entries/{entry}/reject',  [HrDailyEntryController::class, 'approvalReject'])
            ->middleware('can:reject,entry')->name('hr-entries.reject')->whereUuid('entry');

        Route::post('hr-entries/bulk', [HrDailyEntryController::class, 'bulk'])
            ->middleware('can:bulkAction,App\Models\HrDailyEntry')->name('hr-entries.bulk');
        Route::get('hr-entries/trashed', [HrDailyEntryController::class, 'trashed'])
            ->middleware('can:viewTrashed,App\Models\HrDailyEntry')->name('hr-entries.trashed');
        Route::post('hr-entries/{entry}/restore', [HrDailyEntryController::class, 'restore'])
            ->middleware('can:restore,entry')->name('hr-entries.restore')->whereUuid('entry');
        Route::delete('hr-entries/{entry}/force', [HrDailyEntryController::class, 'forceDelete'])
            ->middleware('can:forceDelete,entry')->name('hr-entries.force-delete')->whereUuid('entry');

        Route::get('hr-entries/export/csv', [HrDailyEntryController::class, 'exportCsv'])
            ->middleware('can:export,App\Models\HrDailyEntry')->name('hr-entries.export.csv');
        Route::get('hr-entries/print', [HrDailyEntryController::class, 'print'])
            ->middleware('can:export,App\Models\HrDailyEntry')->name('hr-entries.print');

        Route::get('hr-entries/{entry}/attachments/{key}', [HrDailyEntryController::class, 'downloadAttachment'])
            ->middleware('can:view,entry')->name('hr-entries.attachments.download')->whereUuid('entry');

        Route::get('hr-entries/options/types', [HrDailyEntryController::class, 'typesOptions'])
            ->name('hr-entries.options.types');
        Route::get('hr-entries/options/ga-categories', [HrDailyEntryController::class, 'gaCategoriesOptions'])
            ->name('hr-entries.options.ga-categories');
        Route::get('hr-entries/search/users', [HrDailyEntryController::class, 'searchUsers'])
            ->middleware('can:viewAny,App\Models\User')->name('hr-entries.search.users');

        Route::resource('contracts', EmploymentContractController::class)
            ->parameters(['contracts' => 'employmentContract'])->except(['show']);
        Route::resource('crew-assignments', CrewAssignmentController::class)
            ->parameters(['crew-assignments' => 'crewAssignment'])->except(['show']);
    });

Route::prefix('hr-entries/print-templates')->name('hr-entries.print-templates.')
    ->middleware('can:manage,App\Models\HrDailyEntry')->group(function () {
        Route::get('/', [HrDailyEntryController::class, 'printTemplatesIndex'])->name('index');
        Route::get('/{type}', [HrDailyEntryController::class, 'printTemplatesShow'])->name('show')->where('type', '[A-Za-z0-9_\-]+');
        Route::match(['put', 'patch'], '/{type}', [HrDailyEntryController::class, 'printTemplatesUpsert'])->name('upsert')->where('type', '[A-Za-z0-9_\-]+');
        Route::delete('/{type}', [HrDailyEntryController::class, 'printTemplatesDestroy'])->name('destroy')->where('type', '[A-Za-z0-9_\-]+');
    });

Route::middleware(['auth', 'hasrole:gm|hr'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::resource('payroal', PayroalController::class)
            ->parameters(['payroal' => 'payroal'])
            ->except(['show'])
            ->whereUuid(['payroal']);

        Route::get('payroal/export/csv', [PayroalController::class, 'exportCsv'])->name('payroal.export.csv');
        Route::get('payroal/print',      [PayroalController::class, 'print'])->name('payroal.print');

        Route::post('payroal/{payroal}/lock',   [PayroalController::class, 'lock'])->name('payroal.lock')->whereUuid('payroal');
        Route::post('payroal/{payroal}/unlock', [PayroalController::class, 'unlock'])->name('payroal.unlock')->whereUuid('payroal');

        Route::get('payroal/lookup/by-user', [PayroalController::class, 'lookupByUser'])->name('payroal.lookup.by-user');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/me/payroal', [PayroalProfileController::class, 'edit'])->name('me.payroal.edit');
    Route::match(['put', 'patch'], '/me/payroal', [PayroalProfileController::class, 'update'])->name('me.payroal.update');
    Route::post('/me/payroal/upload', [PayroalProfileController::class, 'upload'])->name('me.payroal.upload');
    Route::get('/me/payroal/download.xls', [PayroalProfileController::class, 'downloadXlsx'])->name('me.payroal.download.xls');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)
        ->parameters(['entries' => 'entry'])
        ->names('manpower.entries');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('manpower/entries', ManpowerEntryController::class)
        ->parameters(['entries' => 'entry'])
        ->names('manpower.entries');
});

Route::middleware(['auth', 'hasrole:gm|manager|hr|hse_officer', 'site.selected'])
    ->prefix('admin/hse')->as('admin.hse.')
    ->group(function () {
        Route::get('ping', fn() => 'OK');

        // Incidents
        Route::resource('incidents', HseIncidentController::class)
            ->parameters(['incidents' => 'incident'])
            ->whereUuid(['incident']);
        Route::post('incidents/{incident}/submit', [HseIncidentController::class, 'submit'])
            ->name('incidents.submit')->whereUuid('incident');
        Route::post('incidents/{incident}/start-investigation', [HseIncidentController::class, 'startInvestigation'])
            ->name('incidents.start-investigation')->whereUuid('incident');
        Route::post('incidents/{incident}/close', [HseIncidentController::class, 'close'])
            ->name('incidents.close')->whereUuid('incident');

        // Investigations
        Route::resource('investigations', HseInvestigationController::class)
            ->parameters(['investigations' => 'investigation'])
            ->whereUuid(['investigation']);
        Route::post('investigations/{investigation}/complete', [HseInvestigationController::class, 'complete'])
            ->name('investigations.complete')->whereUuid('investigation');
        Route::post('investigations/{investigation}/reopen', [HseInvestigationController::class, 'reopen'])
            ->name('investigations.reopen')->whereUuid('investigation');

        // PICA
        Route::resource('picas', HsePicaController::class)
            ->parameters(['picas' => 'pica'])
            ->whereUuid(['pica']);
        Route::post('picas/{pica}/mark-effective', [HsePicaController::class, 'markEffective'])
            ->name('picas.mark-effective')->whereUuid('pica');
        Route::post('picas/{pica}/mark-ineffective', [HsePicaController::class, 'markIneffective'])
            ->name('picas.mark-ineffective')->whereUuid('pica');
        Route::post('picas/{pica}/close', [HsePicaController::class, 'close'])
            ->name('picas.close')->whereUuid('pica');

        // Hazards
        Route::resource('hazards', HseHazardController::class)
            ->parameters(['hazards' => 'hazard'])
            ->whereUuid(['hazard']);
        Route::post('hazards/{hazard}/assign', [HseHazardController::class, 'assign'])
            ->name('hazards.assign')->whereUuid('hazard');
        Route::post('hazards/{hazard}/mitigate', [HseHazardController::class, 'mitigate'])
            ->name('hazards.mitigate')->whereUuid('hazard');
        Route::post('hazards/{hazard}/verify', [HseHazardController::class, 'verify'])
            ->name('hazards.verify')->whereUuid('hazard');
        Route::post('hazards/{hazard}/close', [HseHazardController::class, 'close'])
            ->name('hazards.close')->whereUuid('hazard');

        // Environmental Samples
        Route::resource('environmental-samples', HseEnvSampleController::class)
            ->parameters(['environmental-samples' => 'sample'])
            ->whereUuid(['sample']);

        // Media (polymorphic)
        Route::post('media/{type}/{id}', [HseMediaController::class, 'store'])
            ->name('media.store')
            ->where(['type' => 'incidents|investigations|picas|hazards|environmental-samples', 'id' => '[0-9a-fA-F-]{36}']);
        Route::delete('media/{attachment}', [HseMediaController::class, 'destroy'])
            ->name('media.destroy')->whereUuid('attachment');

        // KPI
        Route::resource('kpi-indicators', HseKpiController::class)
            ->parameters(['kpi-indicators' => 'kpi'])
            ->whereUuid(['kpi']);
        Route::get('kpi-indicators/type/{type}', [HseKpiController::class, 'index'])
            ->name('kpi-indicators.type')
            ->whereIn('type', ['leading', 'lagging', 'operational']);
        Route::get('kpi-indicators/export/csv', [HseKpiController::class, 'exportCsv'])
            ->name('kpi-indicators.export.csv');
        Route::post('kpi-indicators/import', [HseKpiController::class, 'import'])
            ->name('kpi-indicators.import');
    });

/*
|--------------------------------------------------------------------------
| PAYROAL HISTORY (HR/GM/superadmin) — pakai hasrole (BUKAN role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','hasrole:hr|gm|superadmin'])
    ->prefix('admin/payroal/history')->name('admin.payroal_history.')
    ->group(function () {
        Route::get('/',        [PayroalHistoryController::class,'index'])->name('index');
        Route::get('/create',  [PayroalHistoryController::class,'create'])->name('create');
        Route::post('/',       [PayroalHistoryController::class,'store'])->name('store');
        Route::post('/{history}/lock', [PayroalHistoryController::class,'lock'])->name('lock')->whereUuid('history');
        Route::post('/{history}/send', [PayroalHistoryController::class,'sendOne'])->name('sendOne')->whereUuid('history');
        Route::post('/send-bulk',      [PayroalHistoryController::class,'sendBulk'])->name('sendBulk');
    });

/*
|--------------------------------------------------------------------------
| Payslip public token (tanpa login) & list milik user (login)
|--------------------------------------------------------------------------
*/
Route::get('/me/payslip/{token}', function (string $token) {
    $h = PayroalHistory::where('view_token',$token)->firstOrFail();
    return view('my.payslip', ['h'=>$h]);
})->name('my.payslip.view');
Route::get('/_mailtest', function () {
    \Illuminate\Support\Facades\Mail::raw('Test email OK yakk hehehe', function($m) {
        $m->to('imyharis@gmail.com')->subject('Test SMTP');
    });
    return 'Sent (check inbox / spam)';
})->middleware('auth');

require __DIR__ . '/auth.php';
