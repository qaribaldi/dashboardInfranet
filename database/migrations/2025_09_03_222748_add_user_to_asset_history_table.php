<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('asset_history', function (Blueprint $table) {
        $table->string('edited_by')->nullable()->after('asset_id'); 
        // atau kalau kamu lebih suka relasi user_id:
        // $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('asset_history', function (Blueprint $table) {
        $table->dropColumn('edited_by'); 
        // atau kalau pakai relasi, dropForeign dulu
    });
}

};
