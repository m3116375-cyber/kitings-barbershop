<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MasterDataSeeder::class,
        ]);

        // Data contoh (riwayat booking 45 hari terakhir) supaya dashboard & laporan tidak kosong.
        // Matikan dengan SEED_DEMO_DATA=false di file .env sebelum `php artisan migrate:fresh --seed`.
        if (filter_var(env('SEED_DEMO_DATA', true), FILTER_VALIDATE_BOOL)) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
