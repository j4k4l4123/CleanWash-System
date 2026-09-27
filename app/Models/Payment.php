<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'kode_pembayaran',
        'jumlah_bayar',
        'uang_diterima',
        'kembalian',
        'metode_pembayaran',
        'status',
        'tgl_bayar',
        'catatan',
    ];

    protected $casts = [
        'tgl_bayar' => 'datetime',
        'jumlah_bayar' => 'decimal:2',
        'uang_diterima' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
