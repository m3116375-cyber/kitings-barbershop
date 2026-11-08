<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatingHour;
use App\Models\Slot;
use App\Services\SlotGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan ke-7 hari tersedia.
        foreach (range(0, 6) as $d) {
            OperatingHour::firstOrCreate(['day_of_week' => $d]);
        }

        $date = $request->query('date');
        $date = ($date && Carbon::hasFormat($date, 'Y-m-d')) ? $date : now()->toDateString();

        return view('admin.schedule', [
            'hours' => OperatingHour::orderBy('day_of_week')->get()->sortBy(fn ($h) => ($h->day_of_week + 6) % 7),
            'date' => $date,
            'slots' => SlotGenerator::ensureForDate($date),
        ]);
    }

    public function updateHours(Request $request)
    {
        $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.open_time' => ['required', 'date_format:H:i'],
            'hours.*.close_time' => ['required', 'date_format:H:i'],
            'hours.*.slot_duration' => ['required', 'integer', 'min:10', 'max:240'],
            'hours.*.capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        foreach ($request->input('hours') as $day => $row) {
            if (($row['is_open'] ?? '0') == '1' && $row['close_time'] <= $row['open_time']) {
                return back()->withInput()->with('error', 'Jam tutup harus lebih besar dari jam buka (' . OperatingHour::DAYS[(int) $day] . ').');
            }
        }

        foreach ($request->input('hours') as $day => $row) {
            OperatingHour::where('day_of_week', (int) $day)->update([
                'is_open' => ($row['is_open'] ?? '0') == '1',
                'open_time' => $row['open_time'] . ':00',
                'close_time' => $row['close_time'] . ':00',
                'slot_duration' => (int) $row['slot_duration'],
                'capacity' => (int) $row['capacity'],
            ]);
        }

        return back()->with('success', 'Jam operasional disimpan. Slot baru akan dibuat sesuai pengaturan ini; slot yang sudah ada tidak berubah.');
    }

    public function updateSlot(Request $request, Slot $slot)
    {
        $data = $request->validate([
            'capacity' => ['required', 'integer', 'min:' . max(1, $slot->booked_count), 'max:50'],
        ], [
            'capacity.min' => 'Kapasitas tidak boleh lebih kecil dari jumlah pelanggan yang sudah booking (' . $slot->booked_count . ').',
        ]);

        $slot->update(['capacity' => $data['capacity'], 'is_closed' => $request->boolean('is_closed')]);

        return back()->with('success', 'Slot ' . $slot->label . ' diperbarui.');
    }
}
