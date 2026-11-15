@extends('layouts.landing')
@section('title', 'Pesanan Saya')
@section('bg', 'images/hero.jpg')
@section('overlay', 'bg-black/75')
@section('content')
<section class="pt-10 pb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif font-bold text-3xl md:text-4xl">Pesanan <span class="text-gold">Saya</span></h1>
        <p class="mt-2 text-white/75">Pantau status booking dan pembayaran Anda di sini.</p>
    </div>
    <a href="{{ route('booking.create') }}" class="inline-flex items-center gap-2 bg-gold text-black font-semibold px-5 py-2.5 rounded-full hover:opacity-90">
        <i data-lucide="plus" class="w-5 h-5"></i> Booking Baru
    </a>
</section>

<div class="bg-black/60 backdrop-blur border border-white/15 rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-white/10 text-left text-gold">
            <tr>
                <th class="p-3 font-semibold">Kode</th>
                <th class="p-3 font-semibold">Tanggal &amp; Slot</th>
                <th class="p-3 font-semibold">Layanan / Produk</th>
                <th class="p-3 font-semibold">Status</th>
                <th class="p-3 font-semibold">Pembayaran</th>
                <th class="p-3 font-semibold text-right">Total</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr class="border-t border-white/10 align-top hover:bg-white/5">
                <td class="p-3 font-semibold whitespace-nowrap">{{ $o->code }}</td>
                <td class="p-3 whitespace-nowrap">
                    {{ tgl($o->slot->date) }}
                    <div class="text-white/60">{{ $o->slot->label }}</div>
                </td>
                <td class="p-3">
                    {{ $o->bookingServices->pluck('service.name')->implode(', ') }}
                    @if($o->bookingProducts->isNotEmpty())
                        <div class="text-white/60">+ {{ $o->bookingProducts->map(fn ($p) => $p->product->name . ' x' . $p->qty)->implode(', ') }}</div>
                    @endif
                </td>
                <td class="p-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $o->status_class }}">{{ $o->status_label }}</span>
                </td>
                <td class="p-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $o->payment->status_class }}">{{ $o->payment->status_label }}</span>
                    <div class="text-xs text-white/60 mt-1">{{ $o->payment->method_label }}</div>
                </td>
                <td class="p-3 text-right font-semibold text-gold whitespace-nowrap">{{ rupiah($o->total) }}</td>
                <td class="p-3 text-right whitespace-nowrap">
                    @if($o->isActive() && ! $o->payment->isPaid() && $o->payment->method !== 'cod')
                        <a href="{{ route('orders.pay', $o) }}" class="inline-block bg-gold text-black text-xs font-semibold px-3 py-1.5 rounded-full hover:opacity-90">Bayar</a>
                    @endif
                    <a href="{{ route('orders.show', $o) }}" class="inline-block border border-white/40 text-xs font-semibold px-3 py-1.5 rounded-full hover:border-gold hover:text-gold">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-10 text-center">
                    <i data-lucide="calendar-x" class="w-10 h-10 mx-auto text-white/40"></i>
                    <p class="mt-3 text-white/70">Belum ada pesanan.</p>
                    <a href="{{ route('booking.create') }}" class="inline-block mt-4 bg-gold text-black font-semibold px-5 py-2 rounded-full">Buat booking pertama</a>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection