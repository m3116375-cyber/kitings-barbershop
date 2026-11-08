<?php

namespace Database\Seeders;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingManager;
use App\Services\SlotGenerator;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Budi Santoso', 'budi@kitings.test'], ['Citra Lestari', 'citra@kitings.test'], ['Dimas Saputra', 'dimas@kitings.test'], ['Eko Wijaya', 'eko@kitings.test']] as $i => [$name, $email]) {
            User::updateOrCreate(['email' => $email], ['name' => $name, 'phone' => '08130000000' . $i, 'role' => User::ROLE_PELANGGAN, 'password' => 'password']);
        }

        $customers = User::where('role', User::ROLE_PELANGGAN)->get();
        $services = Service::active()->get();
        $products = Product::active()->get();

        // ---- Riwayat 45 hari terakhir (sudah selesai / sebagian dibatalkan) ----
        for ($i = 45; $i >= 1; $i--) {
            $date = now()->subDays($i)->toDateString();
            $slots = SlotGenerator::ensureForDate($date);
            if ($slots->isEmpty()) {
                continue;
            }

            for ($n = mt_rand(0, 3); $n > 0; $n--) {
                $slot = $slots[mt_rand(0, $slots->count() - 1)]->fresh();
                if ($slot->booked_count >= $slot->capacity) {
                    continue;
                }

                $cancelled = mt_rand(1, 10) === 1;
                $picked = $services->shuffle()->take(mt_rand(1, 2));
                $total = $picked->sum('price');

                $booking = new Booking([
                    'user_id' => $customers->random()->id,
                    'slot_id' => $slot->id,
                    'status' => $cancelled ? Booking::DIBATALKAN : Booking::SELESAI,
                    'total' => 0,
                ]);
                $booking->created_at = $booking->updated_at = $date . ' 08:00:00';
                $booking->save();
                $booking->update(['code' => 'BK-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT)]);

                foreach ($picked as $s) {
                    $booking->bookingServices()->create(['service_id' => $s->id, 'price' => $s->price]);
                }
                if (mt_rand(1, 100) <= 40) {
                    $p = $products->random();
                    $booking->bookingProducts()->create(['product_id' => $p->id, 'qty' => 1, 'price' => $p->price]);
                    $total += $p->price;
                }
                $booking->update(['total' => $total]);

                $method = mt_rand(1, 100) <= 60 ? Payment::METHOD_MIDTRANS : Payment::METHOD_COD;
                Payment::create([
                    'booking_id' => $booking->id,
                    'method' => $cancelled ? null : $method,
                    'status' => $cancelled ? Payment::DIBATALKAN : Payment::LUNAS,
                    'amount' => $total,
                    'paid_at' => $cancelled ? null : $date . ' ' . sprintf('%02d:%02d:00', mt_rand(10, 20), mt_rand(0, 59)),
                ]);

                if (! $cancelled) {
                    $slot->increment('booked_count');
                }
            }
        }

        // ---- Pesanan aktif untuk hari-hari mendatang (muncul di dashboard admin) ----
        $manager = app(BookingManager::class);
        for ($i = 1; $i <= 3; $i++) {
            $slots = SlotGenerator::ensureForDate(now()->addDays($i)->toDateString());
            foreach ($slots->take(4) as $k => $slot) {
                try {
                    $b = $manager->create($customers->random(), [$services->random()->id], $slot->id, $k % 2 ? [$products->random()->id => 1] : []);
                    if ($k === 0) {
                        $manager->advance($b, Booking::DIKONFIRMASI);
                    }
                } catch (BookingException $e) {
                    // abaikan data contoh yang gagal
                }
            }
        }
    }
}
