<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingHour extends Model
{
    public const DAYS = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];

    protected $fillable = ['day_of_week', 'is_open', 'open_time', 'close_time', 'slot_duration', 'capacity'];

    protected function casts(): array
    {
        return ['is_open' => 'boolean'];
    }

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '-';
    }
}
