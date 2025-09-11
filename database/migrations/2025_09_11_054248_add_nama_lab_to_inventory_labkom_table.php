<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_labkom', function (Blueprint $table) {
            // tambahkan setelah id_labkom
            $table->string('nama_lab', 100)->nullable()->after('id_pc');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_labkom', function (Blueprint $table) {
            $table->dropColumn('nama_lab');
        });
    }
};
