<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_history', function (Blueprint $table) {
            $table->string('asset_type', 32)->change();
        });
    }

    public function down(): void
    {
        // optional: kembalikan ke enum lama kalau perlu
        // Schema::table('asset_history', function (Blueprint $table) {
        //     $table->enum('asset_type', ['pc','printer','proyektor'])->change();
        // });
    }
};
