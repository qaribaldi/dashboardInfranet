<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Redirect /inventory -> /inventory/pc
Route::redirect('/inventory', '/inventory/pc')->name('inventory.index');

// Resource per jenis aset
Route::prefix('inventory')->group(function () {
    Route::resource('pc', AssetPcController::class);
    Route::resource('printer', AssetPrinterController::class);
    Route::resource('proyektor', AssetProyektorController::class);
});
