<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id(); // primary key
            $table->string('unit_kerja')->nullable();
            $table->string('user')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('ruang')->nullable();
            $table->string('tipe_asset')->nullable();
            $table->string('merk')->nullable();
            $table->string('processor')->nullable();
            $table->string('socket_processor')->nullable();
            $table->string('motherboard')->nullable();
            $table->string('jumlah_slot_ram')->nullable();
            $table->string('total_kapasitas_ram')->nullable();
            $table->string('tipe_ram')->nullable();
            $table->string('ram1')->nullable();
            $table->string('ram2')->nullable();
            $table->string('tipe_storage1')->nullable();
            $table->string('storage1')->nullable();
            $table->string('tipe_storage2')->nullable();
            $table->string('storage2')->nullable();
            $table->string('tipe_storage3')->nullable();
            $table->string('storage3')->nullable();
            $table->string('vga')->nullable();
            $table->string('optical_drive')->nullable();
            $table->string('network_adapter')->nullable();
            $table->string('power_suply')->nullable();
            $table->string('operating_sistem')->nullable();
            $table->string('monitor')->nullable();
            $table->string('keyboard')->nullable();
            $table->string('mouse')->nullable();
            $table->string('tahun_pembelian')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
