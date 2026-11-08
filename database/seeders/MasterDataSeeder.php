<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Jam operasional: setiap hari 09.00-21.00, slot 60 menit, kapasitas 3 pelanggan per slot.
        foreach (range(0, 6) as $day) {
            OperatingHour::updateOrCreate(['day_of_week' => $day], [
                'is_open' => true,
                'open_time' => '09:00:00',
                'close_time' => '21:00:00',
                'slot_duration' => 60,
                'capacity' => 3,
            ]);
        }

        $services = [
            ['Potong Rambut', 'Potong rambut sesuai model pilihan, termasuk keramas singkat.', 40000, 30],
            ['Cukur & Perawatan', 'Potong rambut, cukur jenggot/kumis, dan masker wajah.', 55000, 45],
            ['Pewarnaan Rambut', 'Pewarnaan rambut (belum termasuk produk cat).', 150000, 90],
            ['Creambath', 'Perawatan rambut dan kulit kepala dengan pijat relaksasi.', 60000, 40],
        ];
        foreach ($services as [$name, $desc, $price, $duration]) {
            Service::updateOrCreate(['name' => $name], ['description' => $desc, 'price' => $price, 'duration_minutes' => $duration, 'is_active' => true]);
        }

        $products = [
            ['Cat Rambut', 'Cat rambut semi-permanen, tersedia berbagai warna.', 65000, 12],
            ['Serum Perawatan', 'Serum penguat dan pelembap rambut.', 85000, 8],
            ['Pomade', 'Pomade water-based, daya tahan hingga 8 jam.', 50000, 20],
            ['Hair Tonic', 'Tonik penyegar kulit kepala.', 45000, 10],
        ];
        foreach ($products as [$name, $desc, $price, $stock]) {
            Product::updateOrCreate(['name' => $name], ['description' => $desc, 'price' => $price, 'stock' => $stock, 'is_active' => true]);
        }
    }
}
