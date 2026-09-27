<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'nama_layanan',
        'jenis',
        'harga',
        'durasi_jam',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'durasi_jam' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
