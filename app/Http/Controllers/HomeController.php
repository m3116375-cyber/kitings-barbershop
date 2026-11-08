<?php

namespace App\Http\Controllers;

use App\Models\OperatingHour;
use App\Models\Product;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'services' => Service::active()->orderBy('price')->get(),
            'products' => Product::active()->orderBy('name')->take(3)->get(),
            'hours' => OperatingHour::orderBy('day_of_week')->get()->sortBy(fn ($h) => ($h->day_of_week + 6) % 7),
        ]);
    }

    public function catalog()
    {
        return view('catalog', [
            'services' => Service::active()->orderBy('price')->get(),
            'products' => Product::active()->orderBy('name')->get(),
        ]);
    }
}
