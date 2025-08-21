<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetProyektor extends Model
{
    protected $table = 'asset_proyektor';
    protected $primaryKey = 'id_proyektor';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_proyektor','ruang','nama_ruang','merk','tipe_proyektor','resolusi_max',
        'vga_support','hdmi_support','kabel_hdmi','remote','tahun_pembelian','keterangan_tambahan'
    ];
}
