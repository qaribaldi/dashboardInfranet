<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;
use App\Http\Controllers\AssetAcController;
use App\Http\Controllers\PublicController;

// Landing (publik)
Route::get('/', [PublicController::class, 'landing'])->name('landing');

// Submit edit info (harus login & admin)
Route::post('/landing/info', [PublicController::class, 'updateInfo'])
    ->middleware(['auth','admin'])
    ->name('landing.info.update');

// (Catatan: baris Route::view('/', 'landing') duplikat dengan Route::get('/') di atas, jadi dihapus)

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
 * =========================
 * INVENTORY: ADMIN (CRUD + Tools)
 * =========================
 * - CRUD: create/update/delete untuk tiap resource
 * - Tools: addColumn, Import CSV (form, submit), Template CSV
 */
Route::prefix('inventory')->middleware(['auth','admin'])->name('inventory.')->group(function () {
    // CRUD admin (kecuali index & show yang di bawah untuk user)
    Route::resource('pc',        AssetPcController::class)->except(['index','show']);
    Route::resource('printer',   AssetPrinterController::class)->except(['index','show']);
    Route::resource('proyektor', AssetProyektorController::class)->except(['index','show']);
    Route::resource('ac',        AssetAcController::class)->except(['index','show']);

    // ===== Tools khusus ADMIN =====
    // Add Column
    Route::post('pc/columns',        [AssetPcController::class,        'addColumn'])->name('pc.columns.add');
    Route::post('printer/columns',   [AssetPrinterController::class,   'addColumn'])->name('printer.columns.add');
    Route::post('proyektor/columns', [AssetProyektorController::class, 'addColumn'])->name('proyektor.columns.add');
    Route::post('ac/columns',        [AssetAcController::class,        'addColumn'])->name('ac.columns.add');

    // ====== Import CSV (ADMIN ONLY) ======
    // AC - Import CSV
    Route::get('ac/import',        [AssetAcController::class, 'importForm'])->name('ac.importForm');
    Route::post('ac/import',       [AssetAcController::class, 'importStore'])->name('ac.importStore');
    Route::get('ac/template',      [AssetAcController::class, 'downloadTemplate'])->name('ac.template');
    
    // PC - Import CSV
    Route::get ('pc/import',    [AssetPcController::class,'importForm'])->name('pc.importForm');
    Route::post('pc/import',    [AssetPcController::class,'importStore'])->name('pc.importStore');
    Route::get ('pc/template',  [AssetPcController::class,'downloadTemplate'])->name('pc.template');

    // Printer - Import CSV
    Route::get ('printer/import',    [AssetPrinterController::class,'importForm'])->name('printer.importForm');
    Route::post('printer/import',    [AssetPrinterController::class,'importStore'])->name('printer.importStore');
    Route::get ('printer/template',  [AssetPrinterController::class,'downloadTemplate'])->name('printer.template');

    // Proyektor - Import CSV
    Route::get ('proyektor/import',    [AssetProyektorController::class,'importForm'])->name('proyektor.importForm');
    Route::post('proyektor/import',    [AssetProyektorController::class,'importStore'])->name('proyektor.importStore');
    Route::get ('proyektor/template',  [AssetProyektorController::class,'downloadTemplate'])->name('proyektor.template');
});

/**
 * =========================
 * INVENTORY: USER (index, show)
 * =========================
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
