<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Password semua akun contoh: "password" -- WAJIB diganti sebelum dipakai sungguhan. */
    public function run(): void
    {
        $users = [
            ['Admin Kiting\'s', 'admin@kitings.test', '081200000001', User::ROLE_ADMIN],
            ['Muhammad Ilham Nurdin Harahap', 'owner@kitings.test', '081200000002', User::ROLE_OWNER],
            ['Andi Pratama', 'pelanggan@kitings.test', '081200000003', User::ROLE_PELANGGAN],
        ];

        foreach ($users as [$name, $email, $phone, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'role' => $role, 'password' => 'password']
            );
        }
    }
}
