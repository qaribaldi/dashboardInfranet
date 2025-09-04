<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // CRUD per aset
        $inventoryCrud = [
            // PC
            'inventory.pc.view','inventory.pc.create','inventory.pc.edit','inventory.pc.delete',
            // Printer
            'inventory.printer.view','inventory.printer.create','inventory.printer.edit','inventory.printer.delete',
            // Proyektor
            'inventory.proyektor.view','inventory.proyektor.create','inventory.proyektor.edit','inventory.proyektor.delete',
            // AC
            'inventory.ac.view','inventory.ac.create','inventory.ac.edit','inventory.ac.delete',
            // Hardware
            'inventory.hardware.view','inventory.hardware.create','inventory.hardware.edit','inventory.hardware.delete',
        ];

        // Import/Export per aset
        $perEntityImportExport = [
            'inventory.pc.import','inventory.pc.export',
            'inventory.printer.import','inventory.printer.export',
            'inventory.proyektor.import','inventory.proyektor.export',
            'inventory.ac.import','inventory.ac.export',
            'inventory.hardware.import','inventory.hardware.export',
        ];

        // Manajemen kolom per aset (bisa dipecah add/rename/drop kalau mau)
        $perEntityColumns = [
            'inventory.pc.columns',
            'inventory.printer.columns',
            'inventory.proyektor.columns',
            'inventory.ac.columns',
            'inventory.hardware.columns',
        ];

        // Dashboard
        $dashboardPerms = [
            'dashboard.view', 'dashboard.view.history', 'dashboard.view.lokasi-rawan',
            'dashboard.view.kpi', 'dashboard.view.chart',
        ];

        foreach (array_merge($inventoryCrud, $perEntityImportExport, $perEntityColumns, $dashboardPerms) as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user  = Role::firstOrCreate(['name' => 'user']);

        // Admin dapat semua
        $admin->syncPermissions(Permission::all());

        // USER DEFAULT: tidak punya akses inventory sama sekali.
        // Hanya boleh melihat dashboard basic (opsional: hapus 'dashboard.view' jika mau nol akses total).
        $userDefaultPerms = [
            'dashboard.view',
            // Kalau mau user baru juga bisa lihat lokasi rawan, tinggal tambahkan:
            // 'dashboard.view.lokasi-rawan',
        ];
        $user->syncPermissions(Permission::whereIn('name', $userDefaultPerms)->get());
    }
}
