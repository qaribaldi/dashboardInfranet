<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_labkom', function (Blueprint $table) {
            // PK sama seperti asset_pc
            $table->string('id_pc', 32)->primary();

            $table->string('unit_kerja', 128)->nullable();
            $table->string('user', 128)->nullable();
            $table->string('jabatan', 128)->nullable();
            $table->string('ruang', 64)->nullable();
            $table->string('tipe_asset', 64)->nullable();
            $table->string('merk', 64)->nullable();

            $table->string('processor', 128)->nullable();
            $table->string('socket_processor', 64)->nullable();
            $table->string('motherboard', 128)->nullable();
            $table->integer('jumlah_slot_ram')->nullable();
            $table->string('total_kapasitas_ram', 32)->nullable();
            $table->string('tipe_ram', 32)->nullable();
            $table->string('ram_1', 64)->nullable();
            $table->string('ram_2', 64)->nullable();

            $table->string('tipe_storage_1', 32)->nullable();
            $table->string('storage_1', 64)->nullable();
            $table->string('tipe_storage_2', 32)->nullable();
            $table->string('storage_2', 64)->nullable();
            $table->string('tipe_storage_3', 32)->nullable();
            $table->string('storage_3', 64)->nullable();

            $table->string('vga', 128)->nullable();
            $table->string('optical_drive', 64)->nullable();
            $table->string('network_adapter', 128)->nullable();
            $table->string('power_suply', 64)->nullable();
            $table->string('operating_sistem', 128)->nullable();
            $table->string('monitor', 128)->nullable();
            $table->string('keyboard', 64)->nullable();
            $table->string('mouse', 64)->nullable();

            $table->integer('tahun_pembelian')->nullable();

            // optional status (pakai opsi baru: In use/In store/Service)
            $table->string('status', 16)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_labkom');
    }
};
