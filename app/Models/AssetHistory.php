<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    protected $table = 'asset_history';
    public $timestamps = false;

    protected $fillable = [
        'asset_type','asset_id','action','changes_json','note','created_at'
    ];

    protected $casts = [
        'changes_json' => 'array',
        'created_at' => 'datetime',
    ];
}
