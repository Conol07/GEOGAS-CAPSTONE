<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Lgu\AnalyticsController;
use App\Http\Controllers\Lgu\ComplaintController;
use App\Http\Controllers\Lgu\DashboardController as LguDashboardController;
use App\Http\Controllers\Lgu\ReportController as LguReportController;
use App\Http\Controllers\Lgu\SessionLogController as LguSessionLogController;
use App\Http\Controllers\Lgu\StationController as LguStationController;
use App\Http\Controllers\Lgu\UserController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Station\AnalyticsController as StationAnalyticsController;
use App\Http\Controllers\Station\DashboardController as StationDashboardController;
use App\Http\Controllers\Station\FuelPriceController;
use App\Http\Controllers\Station\FuelTypeController;
use App\Http\Controllers\Station\ReportController as StationReportController;
use App\Http\Controllers\Station\ServiceController;
use App\Http\Controllers\Station\SessionLogController as StationSessionLogController;
use App\Http\Controllers\Station\StaffController;
use App\Http\Controllers\Station\StationInfoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no account required)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/stations', [PublicController::class, 'stations'])->name('stations.index');
Route::get('/stations/{station}', [PublicController::class, 'show'])->name('stations.show');
Route::get('/cheapest', [PublicController::class, 'cheapest'])->name('cheapest');
Route::get('/nearest', [PublicController::class, 'nearest'])->name('nearest'); // JSON, called by geolocation JS
Route::get('/search', [PublicController::class, 'search'])->name('search'); // JSON, called by the nav search bar

Route::get('/complaints/new', [PublicController::class, 'createComplaint'])->name('complaints.create');
Route::post('/complaints', [PublicController::class, 'storeComplaint'])->name('complaints.store');
Route::get('/complaints/confirmation/{referenceNo}', [PublicController::class, 'complaintConfirmation'])->name('complaints.confirmation');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Shared account routes — any authenticated role (manager, staff, LGU admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/account/settings', [AccountController::class, 'edit'])->name('account.settings.edit');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

/*
|--------------------------------------------------------------------------
| Gasoline Station routes (Manager: full access. Staff: permission-gated.)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:manager,staff'])
    ->prefix('station')
    ->name('station.')
    ->group(function () {
        Route::get('/dashboard', [StationDashboardController::class, 'index'])->name('dashboard');

        Route::middleware('permission:update_prices')->group(function () {
            Route::get('/prices/edit', [FuelPriceController::class, 'edit'])->name('prices.edit');
            Route::post('/prices', [FuelPriceController::class, 'update'])->name('prices.update');
        });
        Route::middleware('permission:view_price_history')->group(function () {
            Route::get('/history', [FuelPriceController::class, 'history'])->name('history');
        });
        Route::middleware('permission:view_analytics')->group(function () {
            Route::get('/analytics', [StationAnalyticsController::class, 'index'])->name('analytics.index');
        });
        Route::middleware('permission:manage_services')->group(function () {
            Route::get('/services', [ServiceController::class, 'edit'])->name('services.edit');
            Route::post('/services', [ServiceController::class, 'update'])->name('services.update');
        });
        Route::middleware('permission:edit_station_info')->group(function () {
            Route::get('/info', [StationInfoController::class, 'edit'])->name('info.edit');
            Route::put('/info', [StationInfoController::class, 'update'])->name('info.update');
        });
        Route::middleware('permission:view_reports')->group(function () {
            Route::get('/reports', [StationReportController::class, 'index'])->name('reports.index');
        });
        Route::middleware('permission:update_prices')->group(function () {
            Route::get('/fuel-types/create', [FuelTypeController::class, 'create'])->name('fuel-types.create');
            Route::post('/fuel-types', [FuelTypeController::class, 'store'])->name('fuel-types.store');
        });

        Route::get('/session-logs', [StationSessionLogController::class, 'index'])->name('session-logs.index');
    });

// Staff management — manager only, never staff
Route::middleware(['auth', 'role:manager'])
    ->prefix('station')
    ->name('station.')
    ->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::patch('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle');
        Route::post('/staff/{staff}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    });

/*
|--------------------------------------------------------------------------
| LGU Admin routes — monitoring, analytics, reports, complaints.
| No price approval workflow: managers/staff update prices directly.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:lgu_admin'])
    ->prefix('lgu')
    ->name('lgu.')
    ->group(function () {
        Route::get('/dashboard', [LguDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle');

        Route::get('/stations', [LguStationController::class, 'index'])->name('stations.index');
        Route::get('/stations/create', [LguStationController::class, 'create'])->name('stations.create');
        Route::post('/stations', [LguStationController::class, 'store'])->name('stations.store');
        Route::get('/stations/{station}/edit', [LguStationController::class, 'edit'])->name('stations.edit');
        Route::put('/stations/{station}', [LguStationController::class, 'update'])->name('stations.update');
        Route::delete('/stations/{station}', [LguStationController::class, 'destroy'])->name('stations.destroy');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
        Route::patch('/complaints/{complaint}', [ComplaintController::class, 'updateStatus'])->name('complaints.update');

        Route::get('/reports', [LguReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/fuel-prices', [LguReportController::class, 'fuelPrices'])->name('reports.fuel-prices');
        Route::get('/reports/stations', [LguReportController::class, 'stations'])->name('reports.stations');
        Route::get('/reports/complaints', [LguReportController::class, 'complaints'])->name('reports.complaints');
        Route::get('/reports/station-record', [LguReportController::class, 'stationRecord'])->name('reports.station-record');

        Route::get('/session-logs', [LguSessionLogController::class, 'index'])->name('session-logs.index');
    });
