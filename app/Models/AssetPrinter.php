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

    protected $guarded = [];
}
