<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Integrasi Midtrans Snap (tanpa paket tambahan, memakai HTTP client bawaan Laravel).
 * Dokumentasi: https://docs.midtrans.com/
 */
class MidtransService
{
    public function isConfigured(): bool
    {
        return filled($this->serverKey()) && filled($this->clientKey());
    }

    public function serverKey(): ?string
    {
        return config('services.midtrans.server_key');
    }

    public function clientKey(): ?string
    {
        return config('services.midtrans.client_key');
    }

    public function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production');
    }

    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    private function snapApi(): string
    {
        return $this->isProduction() ? 'https://app.midtrans.com/snap/v1' : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    private function coreApi(): string
    {
        return $this->isProduction() ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
    }

    /** Buat (atau pakai ulang) Snap token untuk pembayaran sebuah booking. */
    public function createSnapToken(Payment $payment): string
    {
        if (! $this->isConfigured()) {
            throw new BookingException('Payment gateway belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY pada file .env.');
        }

        // Pakai ulang token yang masih berlaku agar pelanggan tidak membuat banyak transaksi.
        if ($payment->method === Payment::METHOD_MIDTRANS
            && $payment->status === Payment::BELUM
            && $payment->snap_token
            && $payment->snap_created_at
            && $payment->snap_created_at->gt(now()->subHours(23))) {
            return $payment->snap_token;
        }

        $booking = $payment->booking()->with(['user', 'bookingServices.service', 'bookingProducts.product'])->first();
        $orderId = $booking->code . '-' . now()->format('ymdHis');

        $items = [];
        foreach ($booking->bookingServices as $row) {
            $items[] = ['id' => 'S' . $row->service_id, 'price' => (int) $row->price, 'quantity' => 1, 'name' => Str::limit($row->service->name, 50, '')];
        }
        foreach ($booking->bookingProducts as $row) {
            $items[] = ['id' => 'P' . $row->product_id, 'price' => (int) $row->price, 'quantity' => (int) $row->qty, 'name' => Str::limit($row->product->name, 50, '')];
        }

        $params = [
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => (int) $payment->amount],
            'item_details' => $items,
            'customer_details' => array_filter([
                'first_name' => Str::limit($booking->user->name, 100, ''),
                'email' => $booking->user->email,
                'phone' => $booking->user->phone,
            ]),
            'expiry' => ['unit' => 'hours', 'duration' => 24],
            'callbacks' => ['finish' => route('orders.show', $booking)],
        ];

        $response = Http::withBasicAuth((string) $this->serverKey(), '')
            ->acceptJson()->asJson()->timeout(20)
            ->post($this->snapApi() . '/transactions', $params);

        if ($response->failed()) {
            $msg = $response->json('error_messages.0') ?? ('HTTP ' . $response->status());
            if ($response->status() === 401) {
                $msg = 'Server Key Midtrans salah atau tidak cocok dengan mode sandbox/production.';
            }
            throw new BookingException('Gagal membuat transaksi Midtrans: ' . $msg);
        }

        $token = $response->json('token');
        if (! $token) {
            throw new BookingException('Midtrans tidak mengembalikan token pembayaran.');
        }

        $payment->update([
            'method' => Payment::METHOD_MIDTRANS,
            'midtrans_order_id' => $orderId,
            'snap_token' => $token,
            'snap_created_at' => now(),
            'status' => Payment::BELUM,
        ]);

        return $token;
    }

    /** Tanya status transaksi langsung ke Midtrans (dipakai saat webhook belum/tidak sampai, mis. di localhost). */
    public function fetchStatus(string $orderId): ?array
    {
        $response = Http::withBasicAuth((string) $this->serverKey(), '')
            ->acceptJson()->timeout(15)
            ->get($this->coreApi() . '/' . $orderId . '/status');

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        // Midtrans mengembalikan HTTP 200 dengan status_code 404 bila transaksi belum ada.
        if (($data['status_code'] ?? null) === '404') {
            return null;
        }

        return $data;
    }

    /** Validasi tanda tangan notifikasi: SHA512(order_id + status_code + gross_amount + ServerKey). */
    public function verifySignature(array $data): bool
    {
        $expected = hash('sha512', ($data['order_id'] ?? '') . ($data['status_code'] ?? '') . ($data['gross_amount'] ?? '') . $this->serverKey());

        return hash_equals($expected, (string) ($data['signature_key'] ?? ''));
    }

    /** Terapkan status dari Midtrans (notifikasi/webhook atau hasil fetchStatus) ke tabel payments. */
    public function applyStatus(Payment $payment, array $data): Payment
    {
        $transaction = $data['transaction_status'] ?? null;
        $fraud = $data['fraud_status'] ?? null;

        $new = match (true) {
            $transaction === 'capture' => $fraud === 'challenge' ? Payment::BELUM : Payment::LUNAS,
            $transaction === 'settlement' => Payment::LUNAS,
            in_array($transaction, ['deny', 'cancel', 'failure'], true) => Payment::GAGAL,
            $transaction === 'expire' => Payment::KEDALUWARSA,
            default => Payment::BELUM, // pending
        };

        // Jangan menurunkan status yang sudah lunas.
        if ($payment->isPaid() && $new !== Payment::LUNAS) {
            return $payment;
        }

        $payment->update([
            'method' => Payment::METHOD_MIDTRANS,
            'status' => $new,
            'payload' => $data,
            'paid_at' => $new === Payment::LUNAS ? ($payment->paid_at ?? now()) : null,
        ]);

        return $payment->fresh();
    }
}
