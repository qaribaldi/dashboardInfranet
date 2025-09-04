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

        // Manajemen kolom per aset (pakai 1 kunci payung per aset; jika mau bisa dipecah add/rename/drop)
        $perEntityColumns = [
            'inventory.pc.columns',
            'inventory.printer.columns',
            'inventory.proyektor.columns',
            'inventory.ac.columns',
            'inventory.hardware.columns',
        ];

        // Dashboard
        $dashboardPerms = [
            'dashboard.view', 'dashboard.view.history', 'dashboard.view.lokasi-rawan', 'dashboard.view.kpi', 'dashboard.view.chart',	
        ];

        foreach (array_merge($inventoryCrud, $perEntityImportExport, $perEntityColumns, $dashboardPerms) as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user  = Role::firstOrCreate(['name' => 'user']);

        // admin dapat semua
        $admin->syncPermissions(Permission::all());

        // user default: hanya view inventory + semua view dashboard
        $userDefaultPerms = [
    'inventory.pc.view','inventory.printer.view','inventory.proyektor.view',
    'inventory.ac.view','inventory.hardware.view',
    'dashboard.view',                    // hanya bisa buka halaman
    // kalau mau default-nya lihat lokasi rawan, tambahkan ini:
    // 'dashboard.view.lokasi-rawan',
];
$user->syncPermissions(Permission::whereIn('name', $userDefaultPerms)->get());
    }
}
