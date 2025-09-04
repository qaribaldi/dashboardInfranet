<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicController;

use App\Http\Controllers\AssetPcController;
use App\Http\Controllers\AssetPrinterController;
use App\Http\Controllers\AssetProyektorController;
use App\Http\Controllers\AssetAcController;
use App\Http\Controllers\InventoryHardwareController;

// Admin
use App\Http\Controllers\Admin\UserManagementController;

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
 * Arahkan ke modul pertama yang boleh dilihat user.
 */
Route::get('/inventory', function () {
    $u = auth()->user();

    if ($u->can('inventory.pc.view'))        return redirect()->route('inventory.pc.index');
    if ($u->can('inventory.printer.view'))   return redirect()->route('inventory.printer.index');
    if ($u->can('inventory.proyektor.view')) return redirect()->route('inventory.proyektor.index');
    if ($u->can('inventory.ac.view'))        return redirect()->route('inventory.ac.index');
    if ($u->can('inventory.hardware.view'))  return redirect()->route('inventory.hardware.index');

    abort(403, 'Anda tidak punya izin melihat inventory.');
})->middleware('auth')->name('inventory');

/**
 * ============================================================
 * INVENTORY (CRUD & TOOLS via PERMISSION, BUKAN admin) — TOP
 * ============================================================
 */
Route::prefix('inventory')->middleware(['auth'])->name('inventory.')->group(function () {
    // ===== PC =====
    Route::get('pc/create', [AssetPcController::class, 'create'])
        ->middleware('permission:inventory.pc.create')->name('pc.create');
    Route::post('pc', [AssetPcController::class, 'store'])
        ->middleware('permission:inventory.pc.create')->name('pc.store');

    Route::get('pc/{pc}/edit', [AssetPcController::class, 'edit'])
        ->middleware('permission:inventory.pc.edit')->name('pc.edit');
    Route::put('pc/{pc}', [AssetPcController::class, 'update'])
        ->middleware('permission:inventory.pc.edit')->name('pc.update');

    Route::delete('pc/{pc}', [AssetPcController::class, 'destroy'])
        ->middleware('permission:inventory.pc.delete')->name('pc.destroy');

    // Import CSV + Template (PER ASET)
    Route::get('pc/import', [AssetPcController::class,'importForm'])
        ->middleware('permission:inventory.pc.import')->name('pc.importForm');
    Route::post('pc/import', [AssetPcController::class,'importStore'])
        ->middleware('permission:inventory.pc.import')->name('pc.importStore');
    Route::get('pc/template', [AssetPcController::class,'downloadTemplate'])
        ->middleware('permission:inventory.pc.import')->name('pc.template');

    // Kelola Kolom (PER ASET)
    Route::post  ('pc/columns',        [AssetPcController::class,'addColumn'])
        ->middleware('permission:inventory.pc.columns')->name('pc.columns.add');
    Route::post  ('pc/columns/rename', [AssetPcController::class,'renameColumn'])
        ->middleware('permission:inventory.pc.columns')->name('pc.columns.rename');
    Route::delete('pc/columns/drop',   [AssetPcController::class,'dropColumn'])
        ->middleware('permission:inventory.pc.columns')->name('pc.columns.drop');

    // ===== Printer =====
    Route::get('printer/create', [AssetPrinterController::class, 'create'])
        ->middleware('permission:inventory.printer.create')->name('printer.create');
    Route::post('printer', [AssetPrinterController::class, 'store'])
        ->middleware('permission:inventory.printer.create')->name('printer.store');

    Route::get('printer/{printer}/edit', [AssetPrinterController::class, 'edit'])
        ->middleware('permission:inventory.printer.edit')->name('printer.edit');
    Route::put('printer/{printer}', [AssetPrinterController::class, 'update'])
        ->middleware('permission:inventory.printer.edit')->name('printer.update');

    Route::delete('printer/{printer}', [AssetPrinterController::class, 'destroy'])
        ->middleware('permission:inventory.printer.delete')->name('printer.destroy');

    Route::get('printer/import', [AssetPrinterController::class,'importForm'])
        ->middleware('permission:inventory.printer.import')->name('printer.importForm');
    Route::post('printer/import', [AssetPrinterController::class,'importStore'])
        ->middleware('permission:inventory.printer.import')->name('printer.importStore');
    Route::get('printer/template', [AssetPrinterController::class,'downloadTemplate'])
        ->middleware('permission:inventory.printer.import')->name('printer.template');

    Route::post  ('printer/columns',        [AssetPrinterController::class,'addColumn'])
        ->middleware('permission:inventory.printer.columns')->name('printer.columns.add');
    Route::post  ('printer/columns/rename', [AssetPrinterController::class,'renameColumn'])
        ->middleware('permission:inventory.printer.columns')->name('printer.columns.rename');
    Route::delete('printer/columns/drop',   [AssetPrinterController::class,'dropColumn'])
        ->middleware('permission:inventory.printer.columns')->name('printer.columns.drop');

    // ===== Proyektor =====
    Route::get('proyektor/create', [AssetProyektorController::class, 'create'])
        ->middleware('permission:inventory.proyektor.create')->name('proyektor.create');
    Route::post('proyektor', [AssetProyektorController::class, 'store'])
        ->middleware('permission:inventory.proyektor.create')->name('proyektor.store');

    Route::get('proyektor/{proyektor}/edit', [AssetProyektorController::class, 'edit'])
        ->middleware('permission:inventory.proyektor.edit')->name('proyektor.edit');
    Route::put('proyektor/{proyektor}', [AssetProyektorController::class, 'update'])
        ->middleware('permission:inventory.proyektor.edit')->name('proyektor.update');

    Route::delete('proyektor/{proyektor}', [AssetProyektorController::class, 'destroy'])
        ->middleware('permission:inventory.proyektor.delete')->name('proyektor.destroy');

    Route::get('proyektor/import', [AssetProyektorController::class,'importForm'])
        ->middleware('permission:inventory.proyektor.import')->name('proyektor.importForm');
    Route::post('proyektor/import', [AssetProyektorController::class,'importStore'])
        ->middleware('permission:inventory.proyektor.import')->name('proyektor.importStore');
    Route::get('proyektor/template', [AssetProyektorController::class,'downloadTemplate'])
        ->middleware('permission:inventory.proyektor.import')->name('proyektor.template');

    Route::post  ('proyektor/columns',        [AssetProyektorController::class,'addColumn'])
        ->middleware('permission:inventory.proyektor.columns')->name('proyektor.columns.add');
    Route::post  ('proyektor/columns/rename', [AssetProyektorController::class,'renameColumn'])
        ->middleware('permission:inventory.proyektor.columns')->name('proyektor.columns.rename');
    Route::delete('proyektor/columns/drop',   [AssetProyektorController::class,'dropColumn'])
        ->middleware('permission:inventory.proyektor.columns')->name('proyektor.columns.drop');

    // ===== AC =====
    Route::get('ac/create', [AssetAcController::class, 'create'])
        ->middleware('permission:inventory.ac.create')->name('ac.create');
    Route::post('ac', [AssetAcController::class, 'store'])
        ->middleware('permission:inventory.ac.create')->name('ac.store');

    Route::get('ac/{ac}/edit', [AssetAcController::class, 'edit'])
        ->middleware('permission:inventory.ac.edit')->name('ac.edit');
    Route::put('ac/{ac}', [AssetAcController::class, 'update'])
        ->middleware('permission:inventory.ac.edit')->name('ac.update');

    Route::delete('ac/{ac}', [AssetAcController::class, 'destroy'])
        ->middleware('permission:inventory.ac.delete')->name('ac.destroy');

    Route::get('ac/import', [AssetAcController::class,'importForm'])
        ->middleware('permission:inventory.ac.import')->name('ac.importForm');
    Route::post('ac/import', [AssetAcController::class,'importStore'])
        ->middleware('permission:inventory.ac.import')->name('ac.importStore');
    Route::get('ac/template', [AssetAcController::class,'downloadTemplate'])
        ->middleware('permission:inventory.ac.import')->name('ac.template');

    Route::post  ('ac/columns',        [AssetAcController::class,'addColumn'])
        ->middleware('permission:inventory.ac.columns')->name('ac.columns.add');
    Route::post  ('ac/columns/rename', [AssetAcController::class,'renameColumn'])
        ->middleware('permission:inventory.ac.columns')->name('ac.columns.rename');
    Route::delete('ac/columns/drop',   [AssetAcController::class,'dropColumn'])
        ->middleware('permission:inventory.ac.columns')->name('ac.columns.drop');

    // ===== Hardware =====
    Route::get('hardware/create', [InventoryHardwareController::class, 'create'])
        ->middleware('permission:inventory.hardware.create')->name('hardware.create');
    Route::post('hardware', [InventoryHardwareController::class, 'store'])
        ->middleware('permission:inventory.hardware.create')->name('hardware.store');

    Route::get('hardware/{hardware}/edit', [InventoryHardwareController::class, 'edit'])
        ->middleware('permission:inventory.hardware.edit')->name('hardware.edit');
    Route::put('hardware/{hardware}', [InventoryHardwareController::class, 'update'])
        ->middleware('permission:inventory.hardware.edit')->name('hardware.update');

    Route::delete('hardware/{hardware}', [InventoryHardwareController::class, 'destroy'])
        ->middleware('permission:inventory.hardware.delete')->name('hardware.destroy');

    Route::get('hardware/import', [InventoryHardwareController::class,'importForm'])
        ->middleware('permission:inventory.hardware.import')->name('hardware.importForm');
    Route::post('hardware/import', [InventoryHardwareController::class,'importStore'])
        ->middleware('permission:inventory.hardware.import')->name('hardware.importStore');
    Route::get('hardware/template', [InventoryHardwareController::class,'downloadTemplate'])
        ->middleware('permission:inventory.hardware.import')->name('hardware.template');

    Route::post  ('hardware/columns',        [InventoryHardwareController::class,'addColumn'])
        ->middleware('permission:inventory.hardware.columns')->name('hardware.columns.add');
    Route::post  ('hardware/columns/rename', [InventoryHardwareController::class,'renameColumn'])
        ->middleware('permission:inventory.hardware.columns')->name('hardware.columns.rename');
    Route::delete('hardware/columns/drop',   [InventoryHardwareController::class,'dropColumn'])
        ->middleware('permission:inventory.hardware.columns')->name('hardware.columns.drop');
});

/**
 * ============================================================
 * INVENTORY (VIEW: index/show) — Dipagari permission *.view
 * ============================================================
 * Gantikan blok "READ-ONLY untuk semua user login" menjadi
 * definisi per-modul dengan middleware permission berikut.
 */
Route::prefix('inventory')->name('inventory.')->group(function () {

    // PC
    Route::middleware(['auth','permission:inventory.pc.view'])->group(function () {
        Route::resource('pc', AssetPcController::class)->only(['index','show']);
    });

    // Printer
    Route::middleware(['auth','permission:inventory.printer.view'])->group(function () {
        Route::resource('printer', AssetPrinterController::class)->only(['index','show']);
    });

    // Proyektor
    Route::middleware(['auth','permission:inventory.proyektor.view'])->group(function () {
        Route::resource('proyektor', AssetProyektorController::class)->only(['index','show']);
    });

    // AC
    Route::middleware(['auth','permission:inventory.ac.view'])->group(function () {
        Route::resource('ac', AssetAcController::class)->only(['index','show']);
    });

    // Hardware
    Route::middleware(['auth','permission:inventory.hardware.view'])->group(function () {
        Route::resource('hardware', InventoryHardwareController::class)->only(['index','show']);
    });
});

/**
 * =========================
 * ADMIN: MANAJEMEN USER
 * =========================
 */
Route::prefix('admin')->middleware(['auth','admin'])->name('admin.')->group(function () {
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

// Auth routes (Breeze/Fortify/etc)
require __DIR__.'/auth.php';
