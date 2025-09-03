<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom status (string 20) default 'in_use' jika belum ada
        $tables = [
            'asset_pc',
            'asset_printer',
            'asset_proyektor',
            'asset_ac',
        ];

        foreach ($tables as $t) {
            if (!Schema::hasColumn($t, 'status')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->string('status', 20)->default('in_use');
                });

                // Pastikan baris lama terisi 'in_use'
                DB::table($t)->whereNull('status')->update(['status' => 'in_use']);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'asset_pc',
            'asset_printer',
            'asset_proyektor',
            'asset_ac',
        ];

        foreach ($tables as $t) {
            if (Schema::hasColumn($t, 'status')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};
