<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ⇩⇩ Tambahkan guard ini
        if (Schema::hasTable('site_infos')) {
            return; // tabel sudah ada -> skip create
        }

        Schema::create('site_infos', function (Blueprint $table) {
            $table->id();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Aman-kan juga saat rollback
        if (Schema::hasTable('site_infos')) {
            Schema::dropIfExists('site_infos');
        }
    }
};
