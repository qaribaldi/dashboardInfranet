<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLabkom extends Model
{
    protected $table = 'inventory_labkom';
    protected $primaryKey = 'id_pc';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // kalau punya kolom tanggal tertentu bisa ditambah: 'tanggal_pembelian' => 'date',
    ];
}
