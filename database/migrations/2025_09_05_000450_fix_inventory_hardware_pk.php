<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('inventory_hardware')) return;

        // Pastikan kolom NOT NULL (agar bisa dijadikan PK/UNIQUE)
        try {
            Schema::table('inventory_hardware', function (Blueprint $t) {
                // butuh doctrine/dbal untuk change(); kalau tidak ada, biarkan try/catch
                $t->string('id_hardware', 255)->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            // Lewati jika sudah NOT NULL / dbal belum dipasang
            DB::statement("ALTER TABLE inventory_hardware MODIFY id_hardware VARCHAR(255) NOT NULL");
        }

        // Jadikan PRIMARY KEY (atau minimal UNIQUE) — bungkus try/catch agar idempotent
        try {
            DB::statement("ALTER TABLE inventory_hardware ADD PRIMARY KEY (id_hardware)");
        } catch (\Throwable $e) {
            // Jika sudah ada PK tapi bukan di kolom ini, fallback: buat UNIQUE index
            try { DB::statement("CREATE UNIQUE INDEX inv_hw_id_hardware_unique ON inventory_hardware (id_hardware)"); } catch (\Throwable $ee) {}
        }
    }

    public function down(): void
    {
        // tidak perlu rollback; kalau mau:
        // try { DB::statement('ALTER TABLE inventory_hardware DROP PRIMARY KEY'); } catch (\Throwable $e) {}
        // try { DB::statement('DROP INDEX inv_hw_id_hardware_unique ON inventory_hardware'); } catch (\Throwable $e) {}
    }
};
