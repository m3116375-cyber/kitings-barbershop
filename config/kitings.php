<?php

return [
    // Pelanggan hanya boleh booking maksimal sekian hari ke depan.
    'max_days_ahead' => (int) env('BOOKING_MAX_DAYS_AHEAD', 30),
    'phone' => env('SHOP_PHONE', '-'),
    'address' => env('SHOP_ADDRESS', '-'),
];
