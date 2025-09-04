<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_infos')) {
            Schema::create('site_infos', function (Blueprint $table) {
                $table->id();
                $table->longText('content')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Hanya drop kalau ada
        if (Schema::hasTable('site_infos')) {
            Schema::dropIfExists('site_infos');
        }
    }
};
