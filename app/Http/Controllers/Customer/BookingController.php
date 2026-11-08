<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\OperatingHour;
use App\Models\Product;
use App\Models\Service;
use App\Models\Slot;
use App\Services\BookingManager;
use App\Services\SlotGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create()
    {
        return view('customer.booking', [
            'services' => Service::active()->orderBy('price')->get(),
            'products' => Product::active()->where('stock', '>', 0)->orderBy('name')->get(),
            'minDate' => Carbon::today()->toDateString(),
            'maxDate' => Carbon::today()->addDays(config('kitings.max_days_ahead'))->toDateString(),
        ]);
    }

    /** JSON daftar slot pada satu tanggal (dipakai halaman booking pelanggan dan jadwal ulang admin). */
    public function slots(Request $request)
    {
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);
        $date = Carbon::parse($data['date'])->startOfDay();
        $today = Carbon::today();

        if ($date->lt($today) || $date->gt($today->copy()->addDays(config('kitings.max_days_ahead')))) {
            return response()->json(['slots' => [], 'message' => 'Tanggal di luar rentang booking yang diizinkan.']);
        }

        $hour = OperatingHour::where('day_of_week', $date->dayOfWeek)->first();
        if (! $hour || ! $hour->is_open) {
            return response()->json(['slots' => [], 'message' => 'Barbershop tutup pada hari tersebut.']);
        }

        $slots = SlotGenerator::ensureForDate($date)->map(function (Slot $s) use ($date) {
            $past = $date->isToday() && Carbon::parse($s->date . ' ' . $s->start_time)->lte(now());
            $available = ! $s->is_closed && $s->remaining > 0 && ! $past;

            return [
                'id' => $s->id,
                'label' => $s->label,
                'remaining' => $s->remaining,
                'capacity' => $s->capacity,
                'available' => $available,
                'note' => $s->is_closed ? 'Ditutup' : ($past ? 'Lewat' : ($s->remaining > 0 ? 'Sisa ' . $s->remaining : 'Penuh')),
            ];
        })->values();

        return response()->json(['slots' => $slots, 'message' => $slots->isEmpty() ? 'Belum ada slot pada tanggal ini.' : null]);
    }

    public function store(Request $request, BookingManager $manager)
    {
        $data = $request->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer', 'exists:services,id'],
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
            'products' => ['nullable', 'array'],
            'products.*' => ['nullable', 'integer', 'min:0', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'services.required' => 'Pilih minimal satu layanan.',
            'services.min' => 'Pilih minimal satu layanan.',
            'slot_id.required' => 'Pilih tanggal dan slot waktu terlebih dahulu.',
        ]);

        try {
            $booking = $manager->create(
                $request->user(),
                $data['services'],
                (int) $data['slot_id'],
                $data['products'] ?? [],
                $data['notes'] ?? null,
            );
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.pay', $booking)->with('success', 'Booking berhasil dibuat. Silakan pilih metode pembayaran.');
    }
}
