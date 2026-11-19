@extends('layouts.landing')
@section('title', 'Beranda')
@section('content')
@php
$iconFor = function ($name) {
$n = strtolower($name);
return str_contains($n, 'cukur') ? 'user-round'
: (str_contains($n, 'cream') ? 'droplets'
: (str_contains($n, 'warna') ? 'palette' : 'scissors'));
};
@endphp

<section class="pt-16 md:pt-24 pb-10">
    <h1 data-reveal="up"
        class="font-serif font-bold text-4xl md:text-6xl leading-tight">
        Potong Rambut Rapi,<br><span class="text-gold">Booking Tanpa Antre</span>
    </h1>
    <p data-reveal="up"
        data-reveal-delay="150"
        class="mt-5 max-w-xl text-white/85 text-lg">
        Pilih layanan, tanggal, dan slot waktu. Tambahkan produk perawatan, lalu bayar online atau di tempat setelah dilayani.
    </p>
    <div class="mt-8 flex flex-wrap gap-3" data-reveal="up" data-reveal-delay="300">
        <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 bg-gold text-black font-semibold px-6 py-3 rounded-full hover:opacity-90">
            <i data-lucide="calendar-days" class="w-5 h-5"></i> Booking Sekarang
        </a>
        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 border border-gold/70 px-6 py-3 rounded-full hover:bg-gold/10">
            Lihat Layanan <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </a>
    </div>
</section>

<section class="mt-10">
    <h2 data-reveal="left"
        class="font-serif text-2xl font-semibold border-l-4 border-gold pl-3 mb-4">
        Layanan Kami
    </h2>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4" data-stagger="up">
        @foreach($services as $s)
        <div class="flex items-center gap-4 bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl p-4">
            <i data-lucide="{{ $iconFor($s->name) }}" class="w-10 h-10 text-gold shrink-0"></i>
            <div>
                <div class="font-semibold">{{ $s->name }}</div>
                <div class="text-xs text-white/70">Durasi {{ $s->duration_minutes }} menit</div>
                <div class="text-gold font-bold mt-1">{{ rupiah($s->price) }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<section class="mt-10">
    <h2 class="font-serif text-2xl font-semibold border-l-4 border-gold pl-3 mb-4">Produk Unggulan</h2>
    <div class="grid sm:grid-cols-3 gap-4" data-stagger="up">
        @foreach($products as $p)
        <div class="flex items-center gap-4 bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl p-4">
            @include('partials.product-img', ['product' => $p, 'size' => 'w-20 h-20'])
            <div>
                <div class="font-semibold">{{ $p->name }}</div>
                <div class="text-gold font-bold text-lg">{{ rupiah($p->price) }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<section class="mt-10 grid md:grid-cols-2 gap-4" data-stagger="up">
    <div class="bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold mb-3">Jam Operasional</h2>
        <table class="w-full text-sm">
            @foreach($hours as $h)
            <tr class="border-b border-white/10 last:border-0">
                <td class="py-1.5">{{ $h->day_name }}</td>
                <td class="py-1.5 text-right text-white/80">{{ $h->is_open ? jam($h->open_time) . ' - ' . jam($h->close_time) : 'Tutup' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    <div class="bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold mb-3">Kontak</h2>
        <p class="text-sm text-white/80">Telepon/WA: {{ config('kitings.phone') }}</p>
        <p class="text-sm text-white/80 mt-1">Alamat: {{ config('kitings.address') }}</p>
    </div>
</section>
@endsection