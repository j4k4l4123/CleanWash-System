<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'kode_order',
        'customer_id',
        'service_id',
        'berat_atau_jumlah',
        'harga_per_satuan',
        'total_harga',
        'tgl_masuk',
        'estimasi_selesai',
        'tgl_selesai',
        'tgl_diambil',
        'status_cucian',
        'status_pembayaran',
        'metode_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tgl_masuk' => 'datetime',
        'estimasi_selesai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'tgl_diambil' => 'datetime',
        'berat_atau_jumlah' => 'decimal:2',
        'harga_per_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at', 'asc');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public static function listStatuses(): array
    {
        return [
            'diterima' => 'Diterima',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = self::listStatuses();

        return $statuses[$this->status_cucian] ?? ucfirst(str_replace('_', ' ', $this->status_cucian));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status_cucian) {
            'diterima' => 'bg-amber-100 text-amber-800 border-amber-300',
            'siap_diambil' => 'bg-teal-100 text-teal-800 border-teal-300',
            'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'dibatalkan' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'belum_lunas' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}
