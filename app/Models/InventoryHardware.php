<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryHardware extends Model
{
    protected $table = 'inventory_hardware';

    // PK adalah id_hardware (varchar), bukan auto-increment
    protected $primaryKey = 'id_hardware';
    public $incrementing = false;
    protected $keyType = 'string';

    // Tabel punya created_at & updated_at
    public $timestamps = true;

    // Bebaskan mass assignment (atau ganti ke $fillable jika ingin whitelist)
    protected $guarded = [];

    // Casting yang ada di kolom tabel ini
    protected $casts = [
        'jumlah_stock'      => 'integer',
        'tanggal_pembelian' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Relasi ke banyak PC melalui pivot inventory_hardware_pc.
     * Pivot menyimpan 'tanggal_digunakan'.
     */
    public function pcs()
    {
        return $this->belongsToMany(AssetPc::class, 'inventory_hardware_pc',
                'id_hardware', 'id_pc')
            ->withPivot('tanggal_digunakan')
            ->withTimestamps();
    }

    /* ====== (Opsional) Scopes/helpful accessors ====== */
    public function scopeAvailable($q) { return $q->where('status', 'available'); }
    public function scopeInUse($q)     { return $q->where('status', 'In use'); }
}
