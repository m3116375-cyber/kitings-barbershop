<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DIKONFIRMASI = 'dikonfirmasi';
    public const DILAYANI = 'sedang_dilayani';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::MENUNGGU => ['Menunggu', 'bg-yellow-100 text-yellow-800'],
        self::DIKONFIRMASI => ['Dikonfirmasi', 'bg-blue-100 text-blue-800'],
        self::DILAYANI => ['Sedang dilayani', 'bg-orange-100 text-orange-800'],
        self::SELESAI => ['Selesai', 'bg-green-100 text-green-800'],
        self::DIBATALKAN => ['Dibatalkan', 'bg-red-100 text-red-800'],
    ];

    protected $fillable = ['code', 'user_id', 'slot_id', 'status', 'total', 'notes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function bookingProducts(): HasMany
    {
        return $this->hasMany(BookingProduct::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::MENUNGGU, self::DIKONFIRMASI, self::DILAYANI], true);
    }

    /** Pelanggan boleh membatalkan sendiri selama belum dilayani dan belum lunas. */
    public function canCustomerCancel(): bool
    {
        return in_array($this->status, [self::MENUNGGU, self::DIKONFIRMASI], true)
            && ! ($this->payment && $this->payment->status === Payment::LUNAS);
    }
}
