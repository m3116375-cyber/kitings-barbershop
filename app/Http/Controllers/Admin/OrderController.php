<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingManager;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Booking::with(['user', 'slot', 'payment'])->latest('id');

        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($date = $request->query('date')) {
            $q->whereHas('slot', fn ($s) => $s->where('date', $date));
        }
        if ($keyword = trim((string) $request->query('q'))) {
            $q->where(function ($x) use ($keyword) {
                $x->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$keyword}%"));
            });
        }

        return view('admin.orders.index', [
            'orders' => $q->paginate(15)->withQueryString(),
            'statuses' => Booking::STATUSES,
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'slot', 'payment', 'bookingServices.service', 'bookingProducts.product']);

        return view('admin.orders.show', compact('booking'));
    }

    /** Majukan status: dikonfirmasi / sedang_dilayani / selesai. */
    public function status(Request $request, Booking $booking, BookingManager $manager)
    {
        $data = $request->validate(['to' => ['required', 'in:' . implode(',', [Booking::DIKONFIRMASI, Booking::DILAYANI, Booking::SELESAI])]]);

        try {
            $manager->advance($booking, $data['to']);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status pesanan ' . $booking->code . ' diperbarui.');
    }

    public function cancel(Booking $booking, BookingManager $manager)
    {
        $wasPaid = $booking->payment && $booking->payment->status === Payment::LUNAS;

        try {
            $manager->cancel($booking);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = 'Pesanan ' . $booking->code . ' dibatalkan dan kapasitas slot dikembalikan.';
        if ($wasPaid) {
            $msg .= ' PERHATIAN: pesanan ini sudah dibayar lunas, lakukan refund secara manual (lewat dashboard Midtrans atau tunai).';
        }

        return redirect()->route('admin.orders.show', $booking)->with('success', $msg);
    }

    public function confirmCod(Booking $booking, BookingManager $manager)
    {
        try {
            $manager->confirmCod($booking);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembayaran COD ' . $booking->code . ' dicatat lunas.');
    }

    public function rescheduleForm(Booking $booking)
    {
        $booking->load(['user', 'slot']);

        return view('admin.orders.reschedule', [
            'booking' => $booking,
            'minDate' => now()->toDateString(),
            'maxDate' => now()->addDays(config('kitings.max_days_ahead'))->toDateString(),
        ]);
    }

    public function reschedule(Request $request, Booking $booking, BookingManager $manager)
    {
        $data = $request->validate(['slot_id' => ['required', 'integer', 'exists:slots,id']], [
            'slot_id.required' => 'Pilih tanggal dan slot baru.',
        ]);

        try {
            $manager->reschedule($booking, (int) $data['slot_id']);
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $booking)->with('success', 'Jadwal pesanan berhasil diubah.');
    }
}
