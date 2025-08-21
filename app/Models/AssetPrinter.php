<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetPrinter extends Model
{
    protected $table = 'asset_printer';
    protected $primaryKey = 'id_printer';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_printer','unit_kerja','user','jabatan','ruang','jenis_printer','merk','tipe',
        'scanner','tinta','status_warna','kondisi','tahun_pembelian','keterangan_tambahan'
    ];
}
