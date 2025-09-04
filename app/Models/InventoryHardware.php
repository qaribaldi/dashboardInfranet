<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryHardware extends Model
{
    protected $table = 'inventory_hardware';
    protected $primaryKey = 'id_hardware';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // set true jika kamu pakai timestamps otomatis

    protected $guarded = [];
    protected $fillable = [
  'id_hardware','jenis_hardware','tanggal_pembelian','vendor','jumlah_stock',
  'status','tanggal_digunakan','id_pc','storage_type',
  ];


public function pc()
    {
        return $this->belongsTo(AssetPc::class, 'id_pc', 'id_pc');
    }

}
