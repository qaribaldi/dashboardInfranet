<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicController;

use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;
use App\Http\Controllers\AssetAcController;
use App\Http\Controllers\InventoryHardwareController;

/**
 * =========================
 * LANDING (publik) + INFO
 * =========================
 */
Route::get('/', [PublicController::class, 'landing'])->name('landing');
Route::post('/landing/info', [PublicController::class, 'updateInfo'])
    ->middleware(['auth','admin'])
    ->name('landing.info.update');

/**
 * =========================
 * DASHBOARD
 * =========================
 */
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])
    ->middleware('auth')
    ->name('dashboard.metrics');

Route::delete('/dashboard/clear-history', [DashboardController::class, 'clearHistory'])
    ->middleware('auth')
    ->name('dashboard.clear-history');

/**
 * =========================
 * INVENTORY ROOT
 * =========================
 */
Route::get('/inventory', fn () => redirect()->route('inventory.pc.index'))
    ->middleware('auth')
    ->name('inventory');

/**
 * =========================
 * INVENTORY (ADMIN): CRUD (kecuali index/show), tools kolom, import CSV
 * =========================
 */
Route::prefix('inventory')->middleware(['auth','admin'])->name('inventory.')->group(function () {

    // CRUD (admin)
    Route::resource('pc',        AssetPcController::class)->except(['index','show']);
    Route::resource('printer',   AssetPrinterController::class)->except(['index','show']);
    Route::resource('proyektor', AssetProyektorController::class)->except(['index','show']);
    Route::resource('ac',        AssetAcController::class)->except(['index','show']);
    Route::resource('hardware',  InventoryHardwareController::class)->except(['index','show']);

    // ====== Tools Kolom (ADD / RENAME / DROP) ======
    // PC
    Route::post  ('pc/columns',         [AssetPcController::class,        'addColumn'   ])->name('pc.columns.add');
    Route::post  ('pc/columns/rename',  [AssetPcController::class,        'renameColumn'])->name('pc.columns.rename');
    Route::delete('pc/columns/drop',    [AssetPcController::class,        'dropColumn'  ])->name('pc.columns.drop');

    // Printer
    Route::post  ('printer/columns',         [AssetPrinterController::class,   'addColumn'   ])->name('printer.columns.add');
    Route::post  ('printer/columns/rename',  [AssetPrinterController::class,   'renameColumn'])->name('printer.columns.rename');
    Route::delete('printer/columns/drop',    [AssetPrinterController::class,   'dropColumn'  ])->name('printer.columns.drop');

    // Proyektor
    Route::post  ('proyektor/columns',         [AssetProyektorController::class,'addColumn'   ])->name('proyektor.columns.add');
    Route::post  ('proyektor/columns/rename',  [AssetProyektorController::class,'renameColumn'])->name('proyektor.columns.rename');
    Route::delete('proyektor/columns/drop',    [AssetProyektorController::class,'dropColumn'  ])->name('proyektor.columns.drop');

    // AC
    Route::post  ('ac/columns',         [AssetAcController::class,        'addColumn'   ])->name('ac.columns.add');
    Route::post  ('ac/columns/rename',  [AssetAcController::class,        'renameColumn'])->name('ac.columns.rename');
    Route::delete('ac/columns/drop',    [AssetAcController::class,        'dropColumn'  ])->name('ac.columns.drop');

    // Hardware
    Route::post  ('hardware/columns',         [InventoryHardwareController::class,'addColumn'   ])->name('hardware.columns.add');
    Route::post  ('hardware/columns/rename',  [InventoryHardwareController::class,'renameColumn'])->name('hardware.columns.rename');
    Route::delete('hardware/columns/drop',    [InventoryHardwareController::class,'dropColumn'  ])->name('hardware.columns.drop');

    // ====== Import CSV ======
    // PC
    Route::get ('pc/import',   [AssetPcController::class,'importForm' ])->name('pc.importForm');
    Route::post('pc/import',   [AssetPcController::class,'importStore'])->name('pc.importStore');
    Route::get ('pc/template', [AssetPcController::class,'downloadTemplate'])->name('pc.template');

    // Printer
    Route::get ('printer/import',   [AssetPrinterController::class,'importForm' ])->name('printer.importForm');
    Route::post('printer/import',   [AssetPrinterController::class,'importStore'])->name('printer.importStore');
    Route::get ('printer/template', [AssetPrinterController::class,'downloadTemplate'])->name('printer.template');

    // Proyektor
    Route::get ('proyektor/import',   [AssetProyektorController::class,'importForm' ])->name('proyektor.importForm');
    Route::post('proyektor/import',   [AssetProyektorController::class,'importStore'])->name('proyektor.importStore');
    Route::get ('proyektor/template', [AssetProyektorController::class,'downloadTemplate'])->name('proyektor.template');

    // AC
    Route::get ('ac/import',   [AssetAcController::class,'importForm' ])->name('ac.importForm');
    Route::post('ac/import',   [AssetAcController::class,'importStore'])->name('ac.importStore');
    Route::get ('ac/template', [AssetAcController::class,'downloadTemplate'])->name('ac.template');

    // Hardware
    Route::get ('hardware/import',   [InventoryHardwareController::class,'importForm' ])->name('hardware.importForm');
    Route::post('hardware/import',   [InventoryHardwareController::class,'importStore'])->name('hardware.importStore');
    Route::get ('hardware/template', [InventoryHardwareController::class,'downloadTemplate'])->name('hardware.template');
});

/**
 * =========================
 * INVENTORY (USER): index + show
 * =========================
 */
Route::prefix('inventory')->middleware('auth')->name('inventory.')->group(function () {
    Route::resource('pc',        AssetPcController::class)->only(['index','show']);
    Route::resource('printer',   AssetPrinterController::class)->only(['index','show']);
    Route::resource('proyektor', AssetProyektorController::class)->only(['index','show']);
    Route::resource('ac',        AssetAcController::class)->only(['index','show']);
    Route::resource('hardware',  InventoryHardwareController::class)->only(['index','show']);
});

// Auth routes (Breeze/Fortify/etc)
require __DIR__.'/auth.php';
