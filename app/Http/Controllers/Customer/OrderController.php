<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingManager;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function own(Booking $booking): Booking
    {
        abort_unless($booking->user_id === auth()->id(), 403);

        return $booking->loadMissing(['slot', 'payment', 'bookingServices.service', 'bookingProducts.product']);
    }

    public function index(Request $request)
    {
        $orders = Booking::with(['slot', 'payment', 'bookingServices.service', 'bookingProducts.product'])
            ->where('user_id', $request->user()->id)
            ->latest('id')->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function show(Booking $booking)
    {
        return view('customer.order-show', ['booking' => $this->own($booking)]);
    }

    /** Halaman pilih metode pembayaran (online / COD). */
    public function pay(Booking $booking, MidtransService $midtrans)
    {
        $booking = $this->own($booking);

        if (! $booking->isActive() || $booking->payment->isPaid()) {
            return redirect()->route('orders.show', $booking);
        }

        return view('customer.pay', [
            'booking' => $booking,
            'midtrans' => $midtrans,
        ]);
    }

    public function cancel(Booking $booking, BookingManager $manager)
    {
        $booking = $this->own($booking);

        if (! $booking->canCustomerCancel()) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan sendiri. Hubungi admin barbershop.');
        }

        try {
            $manager->cancel($booking);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Pesanan ' . $booking->code . ' berhasil dibatalkan.');
    }

    /** AJAX: buat Snap token Midtrans. */
    public function snap(Booking $booking, MidtransService $midtrans)
    {
        $booking = $this->own($booking);

        if (! $booking->isActive()) {
            return response()->json(['error' => 'Pesanan sudah tidak aktif.'], 422);
        }
        if ($booking->payment->isPaid()) {
            return response()->json(['error' => 'Pembayaran sudah lunas.'], 422);
        }

        try {
            $token = $midtrans->createSnapToken($booking->payment);
        } catch (BookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Tidak dapat terhubung ke Midtrans. Periksa koneksi internet dan konfigurasi key.'], 500);
        }

        return response()->json(['token' => $token]);
    }

    /** Pelanggan memilih bayar di tempat (COD). */
    public function cod(Booking $booking)
    {
        $booking = $this->own($booking);

        if (! $booking->isActive() || $booking->payment->isPaid()) {
            return redirect()->route('orders.show', $booking);
        }

        $booking->payment->update([
            'method' => Payment::METHOD_COD,
            'status' => Payment::BELUM,
            'snap_token' => null,
            'snap_created_at' => null,
        ]);

        return redirect()->route('orders.show', $booking)
            ->with('success', 'Metode COD dipilih. Silakan bayar di barbershop setelah layanan selesai.');
    }

    /** AJAX: sinkronkan status pembayaran langsung dari Midtrans (berguna saat webhook belum sampai). */
    public function check(Booking $booking, MidtransService $midtrans)
    {
        $booking = $this->own($booking);
        $payment = $booking->payment;

        try {
            if ($payment->midtrans_order_id && $midtrans->isConfigured()) {
                $data = $midtrans->fetchStatus($payment->midtrans_order_id);
                if ($data) {
                    $payment = $midtrans->applyStatus($payment, $data);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['status' => $payment->status, 'label' => $payment->status_label]);
    }
}
