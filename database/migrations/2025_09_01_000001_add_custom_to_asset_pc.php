<?php
// database/migrations/2025_09_01_000000_add_custom_to_asset_pc.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('asset_pc', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_pc', 'custom')) {
                $table->json('custom')->nullable()->after('tahun_pembelian');
            }
        });
    }
    public function down(): void {
        Schema::table('asset_pc', function (Blueprint $table) {
            if (Schema::hasColumn('asset_pc', 'custom')) {
                $table->dropColumn('custom');
            }
        });
    }
};
