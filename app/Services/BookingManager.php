<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh aturan bisnis booking ada di sini: kapasitas slot, stok produk,
 * pembatalan, jadwal ulang, dan perubahan status.
 */
class BookingManager
{
    /** Urutan status yang diperbolehkan (pembatalan ditangani oleh cancel()). */
    private const NEXT_STATUS = [
        Booking::MENUNGGU => Booking::DIKONFIRMASI,
        Booking::DIKONFIRMASI => Booking::DILAYANI,
        Booking::DILAYANI => Booking::SELESAI,
    ];

    /**
     * @param array<int,int>        $serviceIds id layanan yang dipilih
     * @param array<int|string,int> $products   [product_id => qty]
     */
    public function create(User $user, array $serviceIds, int $slotId, array $products = [], ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($user, $serviceIds, $slotId, $products, $notes) {
            // Kunci baris slot agar dua pelanggan tidak bisa mengambil kursi terakhir bersamaan.
            $slot = Slot::lockForUpdate()->find($slotId);
            if (! $slot) {
                throw new BookingException('Slot tidak ditemukan.');
            }
            $this->assertSlotBookable($slot);

            $serviceIds = array_values(array_unique(array_map('intval', $serviceIds)));
            $services = Service::active()->whereIn('id', $serviceIds)->get();
            if ($services->isEmpty() || $services->count() !== count($serviceIds)) {
                throw new BookingException('Layanan yang dipilih tidak valid atau sudah tidak tersedia.');
            }

            $total = (int) $services->sum('price');

            $items = [];
            foreach ($products as $productId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }
                $product = Product::active()->lockForUpdate()->find((int) $productId);
                if (! $product) {
                    throw new BookingException('Produk yang dipilih tidak valid.');
                }
                if ($product->stock < $qty) {
                    throw new BookingException("Stok {$product->name} tidak mencukupi (sisa {$product->stock}).");
                }
                $items[] = [$product, $qty];
                $total += $product->price * $qty;
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'slot_id' => $slot->id,
                'status' => Booking::MENUNGGU,
                'total' => $total,
                'notes' => $notes,
            ]);
            $booking->update(['code' => 'BK-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT)]);

            foreach ($services as $service) {
                $booking->bookingServices()->create(['service_id' => $service->id, 'price' => $service->price]);
            }
            foreach ($items as [$product, $qty]) {
                $booking->bookingProducts()->create(['product_id' => $product->id, 'qty' => $qty, 'price' => $product->price]);
                $product->decrement('stock', $qty);
            }

            Payment::create(['booking_id' => $booking->id, 'status' => Payment::BELUM, 'amount' => $total]);
            $slot->increment('booked_count');

            return $booking;
        });
    }

    public function assertSlotBookable(Slot $slot): void
    {
        if ($slot->is_closed) {
            throw new BookingException('Slot ini sedang ditutup oleh barbershop.');
        }
        if ($slot->booked_count >= $slot->capacity) {
            throw new BookingException('Maaf, slot ini sudah penuh. Silakan pilih slot lain.');
        }

        $today = Carbon::today();
        $date = Carbon::parse($slot->date)->startOfDay();

        if ($date->lt($today)) {
            throw new BookingException('Tanggal slot sudah lewat.');
        }
        if ($date->gt($today->copy()->addDays(config('kitings.max_days_ahead')))) {
            throw new BookingException('Booking hanya dapat dilakukan maksimal ' . config('kitings.max_days_ahead') . ' hari ke depan.');
        }
        if ($date->isSameDay($today) && Carbon::parse($slot->date . ' ' . $slot->start_time)->lte(now())) {
            throw new BookingException('Jam slot ini sudah lewat. Silakan pilih slot berikutnya.');
        }
    }

    /** Batalkan pesanan: kapasitas slot dan stok produk dikembalikan. */
    public function cancel(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $b = Booking::lockForUpdate()->with(['payment', 'bookingProducts'])->findOrFail($booking->id);

            if (! $b->isActive()) {
                throw new BookingException('Pesanan ini sudah selesai atau sudah dibatalkan.');
            }

            $slot = Slot::lockForUpdate()->find($b->slot_id);
            if ($slot && $slot->booked_count > 0) {
                $slot->decrement('booked_count');
            }

            foreach ($b->bookingProducts as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->qty);
            }

            $b->update(['status' => Booking::DIBATALKAN]);

            if ($b->payment && in_array($b->payment->status, [Payment::BELUM, Payment::GAGAL, Payment::KEDALUWARSA], true)) {
                $b->payment->update(['status' => Payment::DIBATALKAN]);
            }

            return $b->fresh(['payment']);
        });
    }

    /** Pindahkan pesanan ke slot lain (jadwal ulang oleh admin). */
    public function reschedule(Booking $booking, int $newSlotId): Booking
    {
        return DB::transaction(function () use ($booking, $newSlotId) {
            $b = Booking::lockForUpdate()->findOrFail($booking->id);

            if (! $b->isActive()) {
                throw new BookingException('Hanya pesanan aktif yang dapat dijadwalkan ulang.');
            }
            if ($b->slot_id === $newSlotId) {
                throw new BookingException('Slot baru sama dengan slot saat ini.');
            }

            $new = Slot::lockForUpdate()->find($newSlotId);
            if (! $new) {
                throw new BookingException('Slot baru tidak ditemukan.');
            }
            $this->assertSlotBookable($new);

            $old = Slot::lockForUpdate()->find($b->slot_id);
            if ($old && $old->booked_count > 0) {
                $old->decrement('booked_count');
            }
            $new->increment('booked_count');

            $b->update(['slot_id' => $new->id]);

            return $b->fresh();
        });
    }

    /** Majukan status: menunggu -> dikonfirmasi -> sedang_dilayani -> selesai. */
    public function advance(Booking $booking, string $to): Booking
    {
        return DB::transaction(function () use ($booking, $to) {
            $b = Booking::lockForUpdate()->findOrFail($booking->id);

            if ((self::NEXT_STATUS[$b->status] ?? null) !== $to) {
                throw new BookingException('Perubahan status tidak valid untuk pesanan ini.');
            }

            $b->update(['status' => $to]);

            return $b;
        });
    }

    /** Admin menandai pembayaran COD sebagai lunas setelah uang diterima di lokasi. */
    public function confirmCod(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $b = Booking::lockForUpdate()->with('payment')->findOrFail($booking->id);
            $payment = $b->payment;

            if (! $payment || $payment->method !== Payment::METHOD_COD) {
                throw new BookingException('Pesanan ini tidak menggunakan metode COD.');
            }
            if ($payment->isPaid()) {
                throw new BookingException('Pembayaran sudah tercatat lunas.');
            }
            if (! in_array($b->status, [Booking::DILAYANI, Booking::SELESAI], true)) {
                throw new BookingException('Pembayaran COD dikonfirmasi setelah layanan dimulai/selesai.');
            }

            $payment->update(['status' => Payment::LUNAS, 'paid_at' => now()]);

            return $b->fresh(['payment']);
        });
    }
}
