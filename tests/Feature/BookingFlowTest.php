<?php

namespace Tests\Feature;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingManager;
use App\Services\MidtransService;
use App\Services\SlotGenerator;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    private function user(): User
    {
        return User::factory()->create(['role' => User::ROLE_PELANGGAN]);
    }

    public function test_slot_penuh_tidak_bisa_dibooking_lagi(): void
    {
        $slot = SlotGenerator::ensureForDate(now()->addDay()->toDateString())->first();
        $slot->update(['capacity' => 1]);
        $service = Service::first();
        $manager = app(BookingManager::class);

        $manager->create($this->user(), [$service->id], $slot->id);

        $this->expectException(BookingException::class);
        $manager->create($this->user(), [$service->id], $slot->id);
    }

    public function test_pembatalan_mengembalikan_kapasitas_dan_stok(): void
    {
        $slot = SlotGenerator::ensureForDate(now()->addDay()->toDateString())->first();
        $service = Service::first();
        $product = Product::first();
        $stock = $product->stock;
        $manager = app(BookingManager::class);

        $booking = $manager->create($this->user(), [$service->id], $slot->id, [$product->id => 2]);
        $this->assertEquals(1, $slot->fresh()->booked_count);
        $this->assertEquals($stock - 2, $product->fresh()->stock);
        $this->assertEquals($service->price + 2 * $product->price, $booking->total);

        $manager->cancel($booking);
        $this->assertEquals(0, $slot->fresh()->booked_count);
        $this->assertEquals($stock, $product->fresh()->stock);
        $this->assertEquals(Booking::DIBATALKAN, $booking->fresh()->status);
    }

    public function test_cod_hanya_bisa_dilunasi_setelah_layanan_dimulai(): void
    {
        $slot = SlotGenerator::ensureForDate(now()->addDay()->toDateString())->first();
        $manager = app(BookingManager::class);
        $booking = $manager->create($this->user(), [Service::first()->id], $slot->id);
        $booking->payment->update(['method' => Payment::METHOD_COD]);

        try {
            $manager->confirmCod($booking);
            $this->fail('Seharusnya ditolak karena layanan belum dimulai.');
        } catch (BookingException $e) {
            $this->assertTrue(true);
        }

        $manager->advance($booking, Booking::DIKONFIRMASI);
        $manager->advance($booking, Booking::DILAYANI);
        $manager->confirmCod($booking);

        $this->assertEquals(Payment::LUNAS, $booking->payment->fresh()->status);
    }

    public function test_notifikasi_midtrans_dengan_signature_valid_melunasi_pembayaran(): void
    {
        config(['services.midtrans.server_key' => 'SB-Mid-server-TEST']);

        $slot = SlotGenerator::ensureForDate(now()->addDay()->toDateString())->first();
        $booking = app(BookingManager::class)->create($this->user(), [Service::first()->id], $slot->id);
        $payment = $booking->payment;
        $payment->update(['method' => Payment::METHOD_MIDTRANS, 'midtrans_order_id' => $booking->code . '-TEST']);

        $payload = [
            'order_id' => $booking->code . '-TEST',
            'status_code' => '200',
            'gross_amount' => $payment->amount . '.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ];
        $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'SB-Mid-server-TEST');

        $this->postJson('/midtrans/notification', $payload)->assertOk();
        $this->assertEquals(Payment::LUNAS, $payment->fresh()->status);

        // signature palsu ditolak
        $payload['signature_key'] = 'palsu';
        $this->postJson('/midtrans/notification', $payload)->assertStatus(403);
    }
}
