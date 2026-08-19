<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FuelPriceVerificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StationController as AdminStationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StationPersonnel\DashboardController as StationDashboardController;
use App\Http\Controllers\StationPersonnel\FuelPriceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no account required)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/stations', [PublicController::class, 'stations'])->name('stations.index');
Route::get('/stations/{station}', [PublicController::class, 'show'])->name('stations.show');
Route::get('/compare', [PublicController::class, 'compare'])->name('compare');
Route::get('/map', [PublicController::class, 'map'])->name('map');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Station personnel routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:station_personnel'])
    ->prefix('station')
    ->name('station.')
    ->group(function () {
        Route::get('/dashboard', [StationDashboardController::class, 'index'])->name('dashboard');
        Route::get('/prices/create', [FuelPriceController::class, 'create'])->name('prices.create');
        Route::post('/prices', [FuelPriceController::class, 'store'])->name('prices.store');
        Route::get('/history', [FuelPriceController::class, 'history'])->name('history');
    });

/*
|--------------------------------------------------------------------------
| Administrator routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle');

        Route::resource('stations', AdminStationController::class)->except(['show']);

        Route::get('/verification', [FuelPriceVerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification/{fuelPrice}/approve', [FuelPriceVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{fuelPrice}/reject', [FuelPriceVerificationController::class, 'reject'])->name('verification.reject');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/stations', [ReportController::class, 'stations'])->name('reports.stations');
        Route::get('/reports/price-history', [ReportController::class, 'priceHistory'])->name('reports.price-history');
    });
