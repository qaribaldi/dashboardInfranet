<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;
use App\Http\Controllers\AssetAcController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Alias: /dashboard -> dashboard yang sama
Route::get('/dashboard', fn () => redirect()->route('dashboard'));

// Redirect /inventory -> /inventory/pc
Route::redirect('/inventory', '/inventory/pc')->name('inventory.index');

// Resource per jenis aset
Route::prefix('inventory')->group(function () {
    Route::resource('pc', AssetPcController::class);
    Route::resource('printer', AssetPrinterController::class);
    Route::resource('proyektor', AssetProyektorController::class);
    Route::resource('ac', AssetAcController::class);
});

// JSON metrics untuk DSS
Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');