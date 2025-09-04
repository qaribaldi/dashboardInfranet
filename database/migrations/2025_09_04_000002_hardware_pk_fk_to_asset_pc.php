<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_hardware', function (Blueprint $table) {
            // pastikan kolom id_pc ada & bertipe string
            if (!Schema::hasColumn('inventory_hardware', 'id_pc')) {
                $table->string('id_pc')->after('id'); // sesuaikan posisi
            }
        });

        // Jika masih ada PK di kolom 'id', lepaskan dulu
        // Catatan: operasi dropPrimary/dropColumn harus hati-hati antar versi.
        // Kita deteksi dan lakukan dengan statement mentah agar universal.
        $hasId = Schema::hasColumn('inventory_hardware', 'id');
        if ($hasId) {
            // Lepas primary key dari id (kalau ada)
            try { DB::statement('ALTER TABLE inventory_hardware DROP PRIMARY KEY'); } catch (\Throwable $e) {}
        }

        // Pastikan id_pc unique sebelum dijadikan PK
        try { DB::statement('CREATE UNIQUE INDEX inv_hw_id_pc_unique ON inventory_hardware (id_pc)'); } catch (\Throwable $e) {}

        // Jadikan id_pc sebagai PRIMARY KEY
        DB::statement('ALTER TABLE inventory_hardware ADD PRIMARY KEY (id_pc)');

        // Hapus kolom id (opsional, jika tidak ingin menyisakan)
        if ($hasId) {
            try { Schema::table('inventory_hardware', fn(Blueprint $t) => $t->dropColumn('id')); } catch (\Throwable $e) {}
        }

        // Tambah FK ke asset_pc
        Schema::table('inventory_hardware', function (Blueprint $table) {
            // drop FK lama kalau ada
            try { $table->dropForeign(['id_pc']); } catch (\Throwable $e) {}
        });
        DB::statement("
            ALTER TABLE inventory_hardware
            ADD CONSTRAINT inv_hw_idpc_fk
            FOREIGN KEY (id_pc) REFERENCES asset_pc(id_pc)
            ON UPDATE CASCADE ON DELETE CASCADE
        ");
    }

    public function down(): void
    {
        // Lepas FK & PK dari id_pc
        try { DB::statement('ALTER TABLE inventory_hardware DROP FOREIGN KEY inv_hw_idpc_fk'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE inventory_hardware DROP PRIMARY KEY'); } catch (\Throwable $e) {}

        // (opsional) kembalikan kolom id auto increment sebagai PK
        Schema::table('inventory_hardware', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_hardware','id')) {
                $table->bigIncrements('id')->first();
            }
        });
    }
};
