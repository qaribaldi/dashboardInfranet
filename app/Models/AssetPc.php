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

    protected $guarded = [];
}


