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
        return $this->hasMany(Order::class);
    }

    public static function generateKodePelanggan(): string
    {
        $nextNumber = (static::max('id') ?? 0) + 1;
        do {
            $code = 'CUST-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::where('kode_pelanggan', $code)->exists());

        return $code;
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
