<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;
use App\Http\Controllers\AssetAcController;

// Landing (publik) — kalau sudah login langsung ke dashboard (opsional)
Route::view('/', 'landing')->name('landing');

// Dashboard (butuh login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Metrics JSON (butuh login)
Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])
    ->middleware('auth')
    ->name('dashboard.metrics');

/**
 * INVENTORY DEFAULT → redirect ke PC
 */
Route::get('/inventory', fn () => redirect()->route('inventory.pc.index'))
    ->middleware('auth')
    ->name('inventory');

/**
 * INVENTORY: ADMIN CRUD — DAFTARKAN DULU
 * (agar /inventory/{resource}/create tidak ketabrak oleh /inventory/{resource}/{id})
 */
Route::prefix('inventory')->middleware(['auth','admin'])->name('inventory.')->group(function () {
    Route::resource('pc',        AssetPcController::class)->except(['index','show']);
    Route::resource('printer',   AssetPrinterController::class)->except(['index','show']);
    Route::resource('proyektor', AssetProyektorController::class)->except(['index','show']);
    Route::resource('ac',        AssetAcController::class)->except(['index','show']);
});

/**
 * INVENTORY: USER (index, show)
 */
Route::prefix('inventory')->middleware('auth')->name('inventory.')->group(function () {
    Route::resource('pc',        AssetPcController::class)->only(['index','show']);
    Route::resource('printer',   AssetPrinterController::class)->only(['index','show']);
    Route::resource('proyektor', AssetProyektorController::class)->only(['index','show']);
    Route::resource('ac',        AssetAcController::class)->only(['index','show']);
});

Route::delete('/dashboard/clear-history', [DashboardController::class, 'clearHistory'])
    ->name('dashboard.clear-history');

// routes auth bawaan Breeze
require __DIR__.'/auth.php';
