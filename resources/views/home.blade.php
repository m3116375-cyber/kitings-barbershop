@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<section class="rounded-xl bg-brand-soft p-8 md:p-12 grid md:grid-cols-2 gap-6 items-center">
    <div>
        <h1 class="text-3xl md:text-4xl font-bold text-brand leading-tight">Potong Rambut Rapi,<br>Booking Tanpa Antre</h1>
        <p class="mt-3 text-stone-600">Pilih layanan, tanggal, dan slot waktu. Tambahkan produk perawatan, lalu bayar online atau di tempat setelah dilayani.</p>
        <div class="mt-5 flex gap-3">
            <a href="{{ route('booking.create') }}" class="px-5 py-2.5 rounded bg-brand text-white font-semibold hover:opacity-90">Booking Sekarang</a>
            <a href="{{ route('catalog') }}" class="px-5 py-2.5 rounded border border-brand text-brand font-semibold hover:bg-white">Lihat Layanan</a>
        </div>
    </div>
    <div class="hidden md:flex justify-center text-8xl select-none">&#9986;&#65039;</div>
</section>

<section class="mt-10">
    <h2 class="text-xl font-bold mb-3">Layanan Kami</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($services as $s)
            <div class="bg-white rounded-lg border border-stone-200 p-4">
                <div class="font-semibold">{{ $s->name }}</div>
                <div class="text-xs text-stone-500">Durasi {{ $s->duration_minutes }} menit</div>
                <div class="mt-2 text-brand font-bold">{{ rupiah($s->price) }}</div>
            </div>
        @endforeach
    </div>
</section>

<section class="mt-10">
    <h2 class="text-xl font-bold mb-3">Produk Unggulan</h2>
    <div class="grid sm:grid-cols-3 gap-4">
        @foreach($products as $p)
            <div class="bg-white rounded-lg border border-stone-200 p-4 flex gap-3">
                @include('partials.product-img', ['product' => $p, 'size' => 'w-16 h-16'])
                <div>
                    <div class="font-semibold">{{ $p->name }}</div>
                    <div class="text-brand font-bold">{{ rupiah($p->price) }}</div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="mt-10 grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg border border-stone-200 p-5">
        <h2 class="text-lg font-bold mb-2">Jam Operasional</h2>
        <table class="text-sm w-full">
            @foreach($hours as $h)
                <tr class="border-b border-stone-100 last:border-0">
                    <td class="py-1.5">{{ $h->day_name }}</td>
                    <td class="py-1.5 text-right">{{ $h->is_open ? jam($h->open_time) . ' - ' . jam($h->close_time) : 'Tutup' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="bg-white rounded-lg border border-stone-200 p-5">
        <h2 class="text-lg font-bold mb-2">Kontak</h2>
        <p class="text-sm text-stone-600">Telepon/WA: {{ config('kitings.phone') }}</p>
        <p class="text-sm text-stone-600 mt-1">Alamat: {{ config('kitings.address') }}</p>
    </div>
</section>
@endsection
