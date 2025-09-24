<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->longText('info_content')->nullable()->after('content');      
            $table->longText('contact_content')->nullable()->after('info_content'); 
        });
    }

    public function down(): void
    {
        Schema::table('site_infos', function (Blueprint $table) {
            $table->dropColumn(['info_content','contact_content']);
        });
    }
};
