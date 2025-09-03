<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_hardware', function (Blueprint $table) {
            $table->string('id_hardware')->primary();
            $table->string('jenis_hardware'); // processor, ram, storage, vga, monitor, motherboard, fan_processor, network_adapter, power_supply, keyboard, mouse
            $table->date('tanggal_pembelian')->nullable();
            $table->string('vendor')->nullable();
            $table->unsignedInteger('jumlah_stock')->default(0);
            $table->string('status')->default('available'); // available | in_use | retired
            $table->date('tanggal_digunakan')->nullable();
            $table->string('id_pc')->nullable();
            $table->json('specs')->nullable(); // field dinamis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_hardware');
    }
};
