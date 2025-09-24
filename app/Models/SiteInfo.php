<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model
{
    protected $fillable = ['content','updated_by','info_content','contact_content'];
    protected $casts = [
        'content' => 'array', // untuk 'contact' (email, telp, alamat, sosmed)
    ];

    public function updater() {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}