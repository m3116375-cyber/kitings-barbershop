<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    // Catatan: kolom `date` sengaja TIDAK di-cast supaya tetap berupa string Y-m-d (aman untuk MySQL maupun SQLite).
    protected $fillable = ['date', 'start_time', 'end_time', 'capacity', 'booked_count', 'is_closed'];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function getLabelAttribute(): string
    {
        return jam($this->start_time) . ' - ' . jam($this->end_time);
    }
}
