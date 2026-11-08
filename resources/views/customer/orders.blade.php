@extends('layouts.app')
@section('title', 'Pesanan Saya')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Riwayat &amp; Status Pesanan</h1>
    <a href="{{ route('booking.create') }}" class="px-4 py-2 rounded bg-brand text-white text-sm font-semibold">+ Booking Baru</a>
</div>

<div class="bg-white border border-stone-200 rounded-lg overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-soft text-left">
            <tr><th class="p-3">Kode</th><th class="p-3">Tanggal &amp; Slot</th><th class="p-3">Layanan / Produk</th><th class="p-3">Status</th><th class="p-3">Pembayaran</th><th class="p-3 text-right">Total</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr class="border-t border-stone-100 align-top">
                <td class="p-3 font-semibold">{{ $o->code }}</td>
                <td class="p-3">{{ tgl($o->slot->date) }}<br><span class="text-stone-500">{{ $o->slot->label }}</span></td>
                <td class="p-3">
                    {{ $o->bookingServices->pluck('service.name')->implode(', ') }}
                    @if($o->bookingProducts->isNotEmpty())
                        <br><span class="text-stone-500">+ {{ $o->bookingProducts->map(fn ($p) => $p->product->name . ' x' . $p->qty)->implode(', ') }}</span>
                    @endif
                </td>
                <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $o->status_class }}">{{ $o->status_label }}</span></td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $o->payment->status_class }}">{{ $o->payment->status_label }}</span>
                    <div class="text-xs text-stone-500 mt-1">{{ $o->payment->method_label }}</div>
                </td>
                <td class="p-3 text-right font-semibold">{{ rupiah($o->total) }}</td>
                <td class="p-3 text-right whitespace-nowrap">
                    @if($o->isActive() && ! $o->payment->isPaid() && ! $o->payment->method)
                        <a href="{{ route('orders.pay', $o) }}" class="text-brand font-semibold">Bayar</a> &middot;
                    @endif
                    <a href="{{ route('orders.show', $o) }}" class="text-brand font-semibold">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-center text-stone-500">Belum ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
