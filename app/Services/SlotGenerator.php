<?php

namespace App\Services;

use App\Models\OperatingHour;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotGenerator
{
    /**
     * Pastikan slot pada tanggal tertentu sudah dibuat berdasarkan jam operasional,
     * lalu kembalikan seluruh slot tanggal tersebut. Slot yang sudah ada tidak diubah.
     */
    public static function ensureForDate(string|Carbon $date): Collection
    {
        $d = Carbon::parse($date)->startOfDay();
        $dateStr = $d->toDateString();

        $hour = OperatingHour::where('day_of_week', $d->dayOfWeek)->first();

        if ($hour && $hour->is_open) {
            $cursor = Carbon::parse($dateStr . ' ' . $hour->open_time);
            $close = Carbon::parse($dateStr . ' ' . $hour->close_time);
            $duration = max(5, (int) $hour->slot_duration);

            while ($cursor->copy()->addMinutes($duration)->lte($close)) {
                $end = $cursor->copy()->addMinutes($duration);

                Slot::firstOrCreate(
                    ['date' => $dateStr, 'start_time' => $cursor->format('H:i:s')],
                    ['end_time' => $end->format('H:i:s'), 'capacity' => $hour->capacity]
                );

                $cursor = $end;
            }
        }

        return Slot::where('date', $dateStr)->orderBy('start_time')->get();
    }
}
