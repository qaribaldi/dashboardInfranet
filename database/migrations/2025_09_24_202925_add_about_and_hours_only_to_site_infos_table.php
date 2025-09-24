<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->longText('about_content')->nullable()->after('contact_content');
            $table->longText('service_hours_content')->nullable()->after('about_content');
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['about_content','service_hours_content']);
        });
    }
};
