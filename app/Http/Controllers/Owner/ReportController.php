<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Slot;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function filters(Request $request): array
    {
        $ok = fn ($v) => $v && Carbon::hasFormat($v, 'Y-m-d');
        $from = $ok($request->query('from')) ? $request->query('from') : now()->startOfMonth()->toDateString();
        $to = $ok($request->query('to')) ? $request->query('to') : now()->endOfMonth()->toDateString();
        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }

        return [
            'from' => $from,
            'to' => $to,
            'service_id' => $request->query('service_id') ? (int) $request->query('service_id') : null,
            'payment_status' => $request->query('payment_status') ?: null,
            'method' => $request->query('method') ?: null,
        ];
    }

    private function query(ReportService $reports, array $f)
    {
        return $reports->bookings($f['from'], $f['to'], $f['service_id'], $f['payment_status'], $f['method'])
            ->with(['user', 'slot', 'payment', 'bookingServices.service', 'bookingProducts.product'])
            ->orderByDesc(Slot::select('date')->whereColumn('slots.id', 'bookings.slot_id'))
            ->orderByDesc('bookings.id');
    }

    public function index(Request $request, ReportService $reports)
    {
        $f = $this->filters($request);
        $base = $reports->bookings($f['from'], $f['to'], $f['service_id'], $f['payment_status'], $f['method']);

        return view('owner.reports', [
            'f' => $f,
            'summary' => $reports->summary($base),
            'bookings' => $this->query($reports, $f)->paginate(20)->withQueryString(),
            'services' => Service::orderBy('name')->get(),
            'paymentStatuses' => Payment::STATUSES,
            'methods' => Payment::METHODS,
        ]);
    }

    /** Ekspor ke CSV (dapat dibuka langsung di Microsoft Excel). */
    public function export(Request $request, ReportService $reports)
    {
        $f = $this->filters($request);
        $rows = $this->query($reports, $f)->get();
        $filename = 'laporan-kitings-' . $f['from'] . '_sd_' . $f['to'] . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Kode', 'Tanggal', 'Jam', 'Pelanggan', 'Layanan', 'Produk', 'Metode', 'Status Pesanan', 'Status Bayar', 'Total (Rp)'], ';');
            $revenue = 0;
            foreach ($rows as $b) {
                if ($b->payment && $b->payment->status === Payment::LUNAS) {
                    $revenue += $b->total;
                }
                fputcsv($out, [
                    $b->code,
                    $b->slot->date,
                    jam($b->slot->start_time),
                    $b->user->name,
                    $b->bookingServices->pluck('service.name')->implode(', '),
                    $b->bookingProducts->map(fn ($p) => $p->product->name . ' x' . $p->qty)->implode(', '),
                    $b->payment->method_label ?? '-',
                    $b->status_label,
                    $b->payment->status_label ?? '-',
                    $b->total,
                ], ';');
            }
            fputcsv($out, ['', '', '', '', '', '', '', '', 'Pendapatan (lunas)', $revenue], ';');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
