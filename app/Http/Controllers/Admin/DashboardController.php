<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        return view('admin.dashboard', [
            'newToday' => Booking::whereDate('created_at', $today)->count(),
            'bookingsToday' => Booking::whereHas('slot', fn ($s) => $s->where('date', $today))
                ->where('status', '!=', Booking::DIBATALKAN)->count(),
            'waiting' => Booking::where('status', Booking::MENUNGGU)->count(),
            'lowStock' => Product::active()->where('stock', '<=', 5)->count(),
            'latest' => Booking::with(['user', 'slot', 'payment'])->latest('id')->take(8)->get(),
        ]);
    }
}
