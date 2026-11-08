@extends('layouts.panel')
@section('title', 'Detail Pesanan')
@section('content')
@php $p = $booking->payment; @endphp
<div class="flex flex-wrap items-center gap-3 mb-4">
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-stone-500 hover:text-brand">&larr; Daftar pesanan</a>
    <h1 class="text-2xl font-bold">{{ $booking->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $booking->status_class }}">{{ $booking->status_label }}</span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">Pelanggan &amp; Jadwal</h2>
            <div class="grid sm:grid-cols-2 gap-2 text-sm">
                <div><span class="text-stone-500">Nama:</span> <span class="font-semibold">{{ $booking->user->name }}</span></div>
                <div><span class="text-stone-500">No. HP:</span> {{ $booking->user->phone ?: '-' }}</div>
                <div><span class="text-stone-500">E-mail:</span> {{ $booking->user->email }}</div>
                <div><span class="text-stone-500">Dibuat:</span> {{ tgl($booking->created_at, 'd M Y H:i') }}</div>
                <div><span class="text-stone-500">Tanggal:</span> <span class="font-semibold">{{ tgl($booking->slot->date, 'l, d F Y') }}</span></div>
                <div><span class="text-stone-500">Slot:</span> <span class="font-semibold">{{ $booking->slot->label }}</span></div>
            </div>
            @if($booking->notes)<p class="text-sm mt-3"><span class="text-stone-500">Catatan:</span> {{ $booking->notes }}</p>@endif
        </section>

        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">Item Pesanan</h2>
            <table class="w-full text-sm">
                @foreach($booking->bookingServices as $row)
                    <tr class="border-b border-stone-100"><td class="py-2">{{ $row->service->name }} <span class="text-xs text-stone-500">(layanan)</span></td><td class="py-2 text-right">{{ rupiah($row->price) }}</td></tr>
                @endforeach
                @foreach($booking->bookingProducts as $row)
                    <tr class="border-b border-stone-100"><td class="py-2">{{ $row->product->name }} x{{ $row->qty }} <span class="text-xs text-stone-500">(produk)</span></td><td class="py-2 text-right">{{ rupiah($row->price * $row->qty) }}</td></tr>
                @endforeach
                <tr class="font-bold"><td class="py-2">Total</td><td class="py-2 text-right text-brand">{{ rupiah($booking->total) }}</td></tr>
            </table>
        </section>
    </div>

    <div class="space-y-6">
        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">Pembayaran</h2>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span class="text-stone-500">Metode</span><span class="font-semibold">{{ $p->method_label }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500">Status</span><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $p->status_class }}">{{ $p->status_label }}</span></div>
                @if($p->midtrans_order_id)<div class="flex justify-between"><span class="text-stone-500">Order ID</span><span class="text-xs">{{ $p->midtrans_order_id }}</span></div>@endif
                @if($p->paid_at)<div class="flex justify-between"><span class="text-stone-500">Dibayar</span><span>{{ tgl($p->paid_at, 'd M Y H:i') }}</span></div>@endif
            </div>
            @if($p->method === 'cod' && ! $p->isPaid() && in_array($booking->status, ['sedang_dilayani', 'selesai']))
                <form method="POST" action="{{ route('admin.orders.cod', $booking) }}" class="mt-3" onsubmit="return confirm('Konfirmasi uang COD sudah diterima?')">@csrf
                    <button class="w-full bg-green-600 text-white py-2 rounded text-sm font-semibold">Konfirmasi COD (Lunas)</button>
                </form>
            @endif
        </section>

        <section class="bg-white border border-stone-200 rounded-lg p-4 space-y-2">
            <h2 class="font-bold mb-1">Tindakan</h2>
            @php $next = ['menunggu' => ['dikonfirmasi', 'Konfirmasi Pesanan'], 'dikonfirmasi' => ['sedang_dilayani', 'Mulai Layani'], 'sedang_dilayani' => ['selesai', 'Tandai Selesai']][$booking->status] ?? null; @endphp
            @if($next)
                <form method="POST" action="{{ route('admin.orders.status', $booking) }}">@csrf
                    <input type="hidden" name="to" value="{{ $next[0] }}">
                    <button class="w-full bg-brand text-white py-2 rounded text-sm font-semibold hover:opacity-90">{{ $next[1] }}</button>
                </form>
            @endif
            @if($booking->isActive())
                <a href="{{ route('admin.orders.reschedule', $booking) }}" class="block text-center border border-brand text-brand py-2 rounded text-sm font-semibold hover:bg-brand-soft">Jadwalkan Ulang</a>
                <form method="POST" action="{{ route('admin.orders.cancel', $booking) }}" onsubmit="return confirm('Batalkan pesanan ini? Kapasitas slot dan stok produk akan dikembalikan.')">@csrf
                    <button class="w-full border border-red-300 text-red-600 py-2 rounded text-sm font-semibold hover:bg-red-50">Batalkan Pesanan</button>
                </form>
            @else
                <p class="text-sm text-stone-500">Pesanan sudah {{ strtolower($booking->status_label) }}.</p>
            @endif
        </section>
    </div>
</div>
@endsection
