<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const BELUM = 'belum_dibayar';
    public const LUNAS = 'lunas';
    public const GAGAL = 'gagal';
    public const KEDALUWARSA = 'kedaluwarsa';
    public const DIBATALKAN = 'dibatalkan';

    public const METHOD_MIDTRANS = 'midtrans';
    public const METHOD_COD = 'cod';

    public const STATUSES = [
        self::BELUM => ['Belum dibayar', 'bg-yellow-100 text-yellow-800'],
        self::LUNAS => ['Lunas', 'bg-green-100 text-green-800'],
        self::GAGAL => ['Gagal', 'bg-red-100 text-red-800'],
        self::KEDALUWARSA => ['Kedaluwarsa', 'bg-red-100 text-red-800'],
        self::DIBATALKAN => ['Dibatalkan', 'bg-gray-200 text-gray-700'],
    ];

    public const METHODS = [
        self::METHOD_MIDTRANS => 'Bayar Online (Midtrans)',
        self::METHOD_COD => 'COD (bayar di tempat)',
    ];

    protected $fillable = ['booking_id', 'method', 'status', 'amount', 'midtrans_order_id', 'snap_token', 'snap_created_at', 'payload', 'paid_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'paid_at' => 'datetime', 'snap_created_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? 'Belum dipilih';
    }

    public function isPaid(): bool
    {
        return $this->status === self::LUNAS;
    }
}
