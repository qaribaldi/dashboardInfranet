<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetPc extends Model
{
    protected $table = 'asset_pc';
    protected $primaryKey = 'id_pc';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pc','unit_kerja','user','jabatan','ruang','tipe_asset','merk','processor',
        'socket_processor','motherboard','jumlah_slot_ram','total_kapasitas_ram','tipe_ram',
        'ram_1','ram_2','tipe_storage_1','storage_1','tipe_storage_2','storage_2',
        // kolom "tipe storage_3" (ada spasi) sengaja di-skip
        'storage_3','vga','optical_drive','network_adapter','power_suply',
        'operating_sistem','monitor','keyboard','mouse','tahun_pembelian'
    ];
}
