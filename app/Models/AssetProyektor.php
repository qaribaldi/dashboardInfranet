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

    protected $guarded = [];
}
