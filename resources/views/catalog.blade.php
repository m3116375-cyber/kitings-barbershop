@extends('layouts.app')
@section('title', 'Layanan & Produk')
@section('content')
<h1 class="text-2xl font-bold mb-4">Daftar Layanan</h1>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($services as $s)
        <div class="bg-white rounded-lg border border-stone-200 p-4">
            <div class="font-semibold">{{ $s->name }}</div>
            @if($s->description)<p class="text-sm text-stone-500 mt-1">{{ $s->description }}</p>@endif
            <div class="text-xs text-stone-500 mt-1">Durasi {{ $s->duration_minutes }} menit</div>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-brand font-bold">{{ rupiah($s->price) }}</span>
                <a href="{{ route('booking.create') }}" class="text-sm px-3 py-1 rounded bg-brand text-white">Pesan</a>
            </div>
        </div>
    @empty
        <p class="text-stone-500">Belum ada layanan.</p>
    @endforelse
</div>

<h2 class="text-2xl font-bold mt-10 mb-4">Produk Perawatan</h2>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse($products as $p)
        <div class="bg-white rounded-lg border border-stone-200 p-3">
            @include('partials.product-img', ['product' => $p])
            <div class="mt-2 font-semibold">{{ $p->name }}</div>
            <div class="text-brand font-bold">{{ rupiah($p->price) }}</div>
            @if($p->stock > 0)
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-semibold {{ $p->stock <= 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">Stok {{ $p->stock }}</span>
            @else
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">Habis</span>
            @endif
        </div>
    @empty
        <p class="text-stone-500">Belum ada produk.</p>
    @endforelse
</div>
@endsection
