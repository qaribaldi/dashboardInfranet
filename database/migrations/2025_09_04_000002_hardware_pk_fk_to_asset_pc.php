<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tujuan revisi:
     * - HANYA memastikan kolom inventory_hardware.id_pc ada dan bertipe VARCHAR(5) NULL
     * - TIDAK membuat PRIMARY KEY / UNIQUE INDEX / FOREIGN KEY apa pun
     *   (karena relasi ke PC sekarang via pivot inventory_hardware_pc)
     */
    public function up(): void
    {
        if (!Schema::hasTable('inventory_hardware')) return;

        // Tambahkan kolom jika belum ada
        if (!Schema::hasColumn('inventory_hardware', 'id_pc')) {
            Schema::table('inventory_hardware', function (Blueprint $table) {
                $table->string('id_pc', 5)->nullable()->after('updated_at');
            });
        } else {
            // Samakan tipe & buat nullable (butuh doctrine/dbal untuk change()).
            Schema::table('inventory_hardware', function (Blueprint $table) {
                try {
                    $table->string('id_pc', 5)->nullable()->change();
                } catch (\Throwable $e) {
                    // Jika doctrine/dbal belum terpasang atau kolom sudah sesuai, abaikan.
                }
            });
        }

        // Pastikan TIDAK ada FK/IDX/PK yang tersisa dari percobaan sebelumnya.
        // Kita drop jika ada—dibungkus try/catch agar aman di semua environment.
        try { \DB::statement('ALTER TABLE inventory_hardware DROP FOREIGN KEY inv_hw_idpc_fk'); } catch (\Throwable $e) {}
        try { \DB::statement('ALTER TABLE inventory_hardware DROP FOREIGN KEY inv_hw_id_pc_fk'); } catch (\Throwable $e) {}
        try { \DB::statement('ALTER TABLE inventory_hardware DROP PRIMARY KEY'); } catch (\Throwable $e) {}
        try { \DB::statement('DROP INDEX inv_hw_id_pc_unique ON inventory_hardware'); } catch (\Throwable $e) {}
        try { \DB::statement('DROP INDEX inv_hw_id_pc_idx ON inventory_hardware'); } catch (\Throwable $e) {}

        // Tidak menambahkan index/PK/FK baru.
    }

    public function down(): void
    {
        // Tidak perlu revert apa-apa. Kalau ingin, bisa hapus kolom id_pc:
        // if (Schema::hasTable('inventory_hardware') && Schema::hasColumn('inventory_hardware','id_pc')) {
        //     Schema::table('inventory_hardware', fn (Blueprint $t) => $t->dropColumn('id_pc'));
        // }
    }
};
