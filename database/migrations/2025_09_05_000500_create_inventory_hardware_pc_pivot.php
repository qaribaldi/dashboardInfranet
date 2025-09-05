<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Buat tabel kalau belum ada
        if (!Schema::hasTable('inventory_hardware_pc')) {
            Schema::create('inventory_hardware_pc', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->string('id_hardware', 255);   // FK ke inventory_hardware.id_hardware
                $t->string('id_pc', 5);           // FK ke asset_pc.id_pc
                $t->date('tanggal_digunakan')->nullable();
                $t->timestamps();
            });
        }

        // 2) Pastikan unique pair (id_hardware,id_pc)
        try {
            DB::statement("CREATE UNIQUE INDEX inv_hw_pc_unique ON inventory_hardware_pc (id_hardware, id_pc)");
        } catch (\Throwable $e) { /* sudah ada */ }

        // 3) Tambahkan FK (abaikan kalau sudah ada)
        // 3a) FK ke inventory_hardware(id_hardware)
        try {
            DB::statement("
                ALTER TABLE inventory_hardware_pc
                ADD CONSTRAINT inventory_hardware_pc_id_hardware_foreign
                FOREIGN KEY (id_hardware) REFERENCES inventory_hardware(id_hardware)
                ON UPDATE CASCADE ON DELETE CASCADE
            ");
        } catch (\Throwable $e) { /* sudah ada / aman diabaikan */ }

        // 3b) FK ke asset_pc(id_pc)
        try {
            DB::statement("
                ALTER TABLE inventory_hardware_pc
                ADD CONSTRAINT inventory_hardware_pc_id_pc_foreign
                FOREIGN KEY (id_pc) REFERENCES asset_pc(id_pc)
                ON UPDATE CASCADE ON DELETE RESTRICT
            ");
        } catch (\Throwable $e) { /* sudah ada */ }

        // 4) (opsional) backfill dari kolom lama jika pivot masih kosong
        try {
            $count = (int) DB::table('inventory_hardware_pc')->count();
            if ($count === 0 && Schema::hasColumn('inventory_hardware', 'id_pc')) {
                DB::statement("
                    INSERT IGNORE INTO inventory_hardware_pc (id_hardware, id_pc, tanggal_digunakan, created_at, updated_at)
                    SELECT ih.id_hardware, LEFT(ih.id_pc,5), ih.tanggal_digunakan, NOW(), NOW()
                    FROM inventory_hardware ih
                    WHERE ih.id_pc IS NOT NULL AND ih.id_pc <> ''
                ");
            }
        } catch (\Throwable $e) { /* abaikan bila tidak perlu */ }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_hardware_pc');
    }
};
