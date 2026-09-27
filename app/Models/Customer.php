<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_hp',
        'alamat',
        'catatan',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getTotalSpendingAttribute(): float
    {
        return (float) $this->orders()->where('status_pembayaran', 'lunas')->sum('total_harga');
    }
}
