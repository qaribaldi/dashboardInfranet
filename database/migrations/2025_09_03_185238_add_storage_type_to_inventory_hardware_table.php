<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_hardware', function (Blueprint $table) {
            // pakai string agar fleksibel, nullable biar aman untuk data lama
            $table->string('storage_type', 10)->nullable()->after('jenis_hardware');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_hardware', function (Blueprint $table) {
            $table->dropColumn('storage_type');
        });
    }
};

