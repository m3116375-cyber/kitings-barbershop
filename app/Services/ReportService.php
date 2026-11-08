<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /** Query booking berdasarkan tanggal slot dan filter opsional. */
    public function bookings(string $from, string $to, ?int $serviceId = null, ?string $paymentStatus = null, ?string $method = null): Builder
    {
        $q = Booking::query()->whereHas('slot', fn ($s) => $s->whereBetween('date', [$from, $to]));

        if ($serviceId) {
            $q->whereHas('bookingServices', fn ($s) => $s->where('service_id', $serviceId));
        }
        if ($paymentStatus) {
            $q->whereHas('payment', fn ($p) => $p->where('status', $paymentStatus));
        }
        if ($method) {
            $q->whereHas('payment', fn ($p) => $p->where('method', $method));
        }

        return $q;
    }

    /** @return array{count:int, revenue:int} booking aktif (tidak dibatalkan) & pendapatan (pembayaran lunas) */
    public function summary(Builder $q): array
    {
        $count = (clone $q)->where('status', '!=', Booking::DIBATALKAN)->count();
        $revenue = (int) Payment::where('status', Payment::LUNAS)
            ->whereIn('booking_id', (clone $q)->select('bookings.id'))
            ->sum('amount');

        return ['count' => $count, 'revenue' => $revenue];
    }

    public function topServices(string $from, string $to, int $limit = 5)
    {
        return DB::table('booking_services as bs')
            ->join('bookings as b', 'b.id', '=', 'bs.booking_id')
            ->join('slots as s', 's.id', '=', 'b.slot_id')
            ->join('services as sv', 'sv.id', '=', 'bs.service_id')
            ->where('b.status', '!=', Booking::DIBATALKAN)
            ->whereBetween('s.date', [$from, $to])
            ->groupBy('sv.id', 'sv.name')
            ->selectRaw('sv.name as name, COUNT(*) as total')
            ->orderByDesc('total')->limit($limit)->get();
    }

    public function topProducts(string $from, string $to, int $limit = 5)
    {
        return DB::table('booking_products as bp')
            ->join('bookings as b', 'b.id', '=', 'bp.booking_id')
            ->join('slots as s', 's.id', '=', 'b.slot_id')
            ->join('products as p', 'p.id', '=', 'bp.product_id')
            ->where('b.status', '!=', Booking::DIBATALKAN)
            ->whereBetween('s.date', [$from, $to])
            ->groupBy('p.id', 'p.name')
            ->selectRaw('p.name as name, SUM(bp.qty) as total')
            ->orderByDesc('total')->limit($limit)->get();
    }

    /** Pendapatan (pembayaran lunas) per minggu untuk grafik. */
    public function weeklyRevenue(int $weeks = 8): array
    {
        $start = Carbon::today()->startOfWeek()->subWeeks($weeks - 1);
        $buckets = [];
        for ($i = 0; $i < $weeks; $i++) {
            $buckets[$start->copy()->addWeeks($i)->toDateString()] = 0;
        }

        Payment::where('status', Payment::LUNAS)->where('paid_at', '>=', $start)->get(['amount', 'paid_at'])
            ->each(function ($p) use (&$buckets) {
                $key = $p->paid_at->copy()->startOfWeek()->toDateString();
                if (isset($buckets[$key])) {
                    $buckets[$key] += $p->amount;
                }
            });

        return [
            'labels' => array_map(fn ($d) => tgl($d, 'd M'), array_keys($buckets)),
            'values' => array_values($buckets),
        ];
    }

    /** Persentase keterisian slot per hari dalam seminggu (Senin..Minggu). */
    public function occupancyByDay(string $from, string $to): array
    {
        $names = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 0 => 'Min'];
        $cap = array_fill(0, 7, 0);
        $booked = array_fill(0, 7, 0);

        Slot::whereBetween('date', [$from, $to])->where('is_closed', false)->get(['date', 'capacity', 'booked_count'])
            ->each(function ($s) use (&$cap, &$booked) {
                $d = Carbon::parse($s->date)->dayOfWeek;
                $cap[$d] += $s->capacity;
                $booked[$d] += $s->booked_count;
            });

        $out = ['labels' => [], 'values' => []];
        foreach ($names as $d => $label) {
            $out['labels'][] = $label;
            $out['values'][] = $cap[$d] > 0 ? (int) round($booked[$d] / $cap[$d] * 100) : 0;
        }

        return $out;
    }
}
