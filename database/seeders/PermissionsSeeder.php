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
            // LABKOM 
            'inventory.labkom.view','inventory.labkom.create','inventory.labkom.edit','inventory.labkom.delete',
        ];

        // Import/Export per aset
        $perEntityImportExport = [
            'inventory.pc.import','inventory.pc.export',
            'inventory.printer.import','inventory.printer.export',
            'inventory.proyektor.import','inventory.proyektor.export',
            'inventory.ac.import','inventory.ac.export',
            'inventory.hardware.import','inventory.hardware.export',
            'inventory.labkom.import','inventory.labkom.export',
        ];

        // Manajemen kolom per aset
        $perEntityColumns = [
            'inventory.pc.columns',
            'inventory.printer.columns',
            'inventory.proyektor.columns',
            'inventory.ac.columns',
            'inventory.hardware.columns',
            'inventory.labkom.columns',
        ];

        // Dashboard
        $dashboardPerms = [
            'dashboard.view', 'dashboard.view.history', 'dashboard.view.lokasi-rawan',
            'dashboard.view.kpi', 'dashboard.view.chart',
        ];

        // Dashboard history per tipe aset
        $dashboardHistoryPerType = [
            'dashboard.history.pc',
            'dashboard.history.printer',
            'dashboard.history.proyektor',
            'dashboard.history.ac',
        ];

        // Backup
        $backupPerms = [
            'backup.download',
        ];

        //landing page
        $siteInfoPerms = [
            'siteinfo.view',
            'siteinfo.manage',
        ];

        // Buat semua permission
        foreach (array_merge(
            $inventoryCrud,
            $perEntityImportExport,
            $perEntityColumns,
            $dashboardPerms,
            $dashboardHistoryPerType,
            $backupPerms,
            $siteInfoPerms
        ) as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user  = Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);

        // Admin dapat semua (termasuk backup.download)
        $admin->syncPermissions(Permission::all());

        // USER DEFAULT: hanya dashboard basic (opsional)
        $userDefaultPerms = [
            'dashboard.view',
        ];
        $user->syncPermissions(Permission::whereIn('name', $userDefaultPerms)->get());
    }
}
