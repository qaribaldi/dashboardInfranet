<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAc extends Model
{
     protected $table = 'asset_ac';
    protected $primaryKey = 'id_ac';
    public $incrementing = false;   // contoh: AC01 -> string
    protected $keyType = 'string';
    public $timestamps = false; 

    protected $fillable = [
        'id_ac','unit_kerja','ruang','tipe_asset','merk','ukuran_pk',
        'kondisi','remote','tahun_pembelian','keterangan'
    ];

    public function getRouteKeyName()
    {
        return 'id_ac';
    }
}
