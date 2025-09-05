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

    // open mass assignment (atau ganti ke $fillable sesuai kebutuhan)
    protected $guarded = [];

    /**
     * Hardware yang terpasang pada PC ini (pivot: inventory_hardware_pc).
     */
    public function hardwareItems()
    {
        return $this->belongsToMany(InventoryHardware::class, 'inventory_hardware_pc',
                'id_pc', 'id_hardware')
            ->withPivot('tanggal_digunakan')
            ->withTimestamps();
    }

    /* ==== (Opsional) helpers ==== */

    /** Scope: hanya PC yang sedang dipakai oleh SKU tertentu */
    public function scopeUsedByHardware($q, string $idHardware)
    {
        return $q->whereHas('hardwareItems', fn($qq) => $qq->where('inventory_hardware.id_hardware', $idHardware));
    }

    /** Scope: PC yang belum terpakai oleh SKU tertentu (untuk dropdown multi-select yang “kosong”) */
    public function scopeFreeForHardware($q, string $idHardware)
    {
        return $q->whereDoesntHave('hardwareItems', fn($qq) => $qq->where('inventory_hardware.id_hardware', $idHardware));
    }
}
