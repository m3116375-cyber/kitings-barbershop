@extends('layouts.landing')
@section('title', 'Layanan & Produk')
@section('bg', 'images/layanan.jpg')
@section('content')
@php
    $iconFor = function ($name) {
        $n = strtolower($name);
        return str_contains($n, 'cukur') ? 'user-round'
            : (str_contains($n, 'cream') ? 'droplets'
            : (str_contains($n, 'warna') ? 'palette' : 'scissors'));
    };
@endphp

<section class="pt-12 md:pt-16">
    <h1 class="font-serif font-bold text-3xl md:text-5xl">
        Layanan <span class="text-gold">&amp; Produk</span>
    </h1>
    <p class="mt-3 max-w-xl text-white/80">Pilih perawatan favorit Anda, lalu booking tanggal dan slot waktunya.</p>
</section>

<section class="mt-10">
    <h2 class="font-serif text-2xl font-semibold border-l-4 border-gold pl-3 mb-4">Layanan Kami</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($services as $s)
            <div class="flex flex-col bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <i data-lucide="{{ $iconFor($s->name) }}" class="w-10 h-10 text-gold shrink-0"></i>
                    <div>
                        <div class="font-semibold text-lg">{{ $s->name }}</div>
                        <div class="text-xs text-white/60">Durasi {{ $s->duration_minutes }} menit</div>
                    </div>
                </div>
                @if($s->description)
                    <p class="text-sm text-white/75 mt-3 flex-1">{{ $s->description }}</p>
                @else
                    <div class="flex-1"></div>
                @endif
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-gold font-bold text-xl">{{ rupiah($s->price) }}</span>
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-1 bg-gold text-black text-sm font-semibold px-4 py-2 rounded-full hover:opacity-90">
                        Pesan <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        @empty
            <p class="text-white/70">Belum ada layanan.</p>
        @endforelse
    </div>
</section>

<section class="mt-12">
    <h2 class="font-serif text-2xl font-semibold border-l-4 border-gold pl-3 mb-4">Produk Perawatan</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($products as $p)
            <div class="flex flex-col bg-black/40 backdrop-blur-sm border border-white/15 rounded-xl overflow-hidden">
                @if($p->image_url)
                    <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-44 object-cover">
                @else
                    <div class="w-full h-44 bg-white/10 flex items-center justify-center text-white/40">
                        <i data-lucide="image" class="w-10 h-10"></i>
                    </div>
                @endif
                <div class="p-4 flex-1 flex flex-col">
                    <div class="font-semibold">{{ $p->name }}</div>
                    @if($p->description)
                        <p class="text-xs text-white/65 mt-1 flex-1">{{ $p->description }}</p>
                    @else
                        <div class="flex-1"></div>
                    @endif
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-gold font-bold text-lg">{{ rupiah($p->price) }}</span>
                        @if($p->stock <= 0)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/20 text-red-300 border border-red-400/40">Habis</span>
                        @elseif($p->stock <= 5)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-300 border border-yellow-400/40">Sisa {{ $p->stock }}</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-500/20 text-green-300 border border-green-400/40">Tersedia</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-white/70">Belum ada produk.</p>
        @endforelse
    </div>
    <p class="text-sm text-white/65 mt-4">Produk dapat ditambahkan saat melakukan booking.</p>
</section>
@endsection