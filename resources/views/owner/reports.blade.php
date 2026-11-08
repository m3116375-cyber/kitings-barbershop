@extends('layouts.panel')
@section('title', 'Laporan')
@section('content')
<div class="no-print flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Laporan</h1>
    <div class="flex gap-2">
        <a href="{{ route('owner.reports.export', request()->query()) }}" class="px-4 py-2 rounded border border-brand text-brand text-sm font-semibold hover:bg-brand-soft">Ekspor Excel (CSV)</a>
        <button onclick="window.print()" class="px-4 py-2 rounded border border-brand text-brand text-sm font-semibold hover:bg-brand-soft">Cetak / Simpan PDF</button>
    </div>
</div>

<form method="GET" class="no-print bg-white rounded-lg border border-stone-200 p-4 mb-4 grid sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end text-sm">
    <div><label class="block text-xs font-semibold mb-1">Dari</label><input type="date" name="from" value="{{ $f['from'] }}" class="w-full border border-stone-300 rounded px-2 py-2"></div>
    <div><label class="block text-xs font-semibold mb-1">Sampai</label><input type="date" name="to" value="{{ $f['to'] }}" class="w-full border border-stone-300 rounded px-2 py-2"></div>
    <div>
        <label class="block text-xs font-semibold mb-1">Layanan</label>
        <select name="service_id" class="w-full border border-stone-300 rounded px-2 py-2">
            <option value="">Semua</option>
            @foreach($services as $s)<option value="{{ $s->id }}" @selected($f['service_id'] === $s->id)>{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold mb-1">Status bayar</label>
        <select name="payment_status" class="w-full border border-stone-300 rounded px-2 py-2">
            <option value="">Semua</option>
            @foreach($paymentStatuses as $k => [$label])<option value="{{ $k }}" @selected($f['payment_status'] === $k)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold mb-1">Metode</label>
        <select name="method" class="w-full border border-stone-300 rounded px-2 py-2">
            <option value="">Semua</option>
            @foreach($methods as $k => $label)<option value="{{ $k }}" @selected($f['method'] === $k)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <button class="px-4 py-2 rounded bg-brand text-white font-semibold">Terapkan</button>
</form>

<div class="mb-2 text-sm text-stone-600">
    <div class="font-bold text-base">Laporan Booking &amp; Pendapatan &mdash; Kiting's Barbershop</div>
    Periode {{ tgl($f['from']) }} &ndash; {{ tgl($f['to']) }}
</div>
<div class="grid grid-cols-2 gap-4 mb-4 max-w-xl">
    <div class="bg-white rounded-lg border border-stone-200 p-3"><div class="text-xs text-stone-500">Total Booking (tidak dibatalkan)</div><div class="text-xl font-bold text-brand">{{ $summary['count'] }}</div></div>
    <div class="bg-white rounded-lg border border-stone-200 p-3"><div class="text-xs text-stone-500">Pendapatan (lunas)</div><div class="text-xl font-bold text-brand">{{ rupiah($summary['revenue']) }}</div></div>
</div>

<div class="bg-white rounded-lg border border-stone-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-soft text-left">
            <tr><th class="p-3">Kode</th><th class="p-3">Tanggal</th><th class="p-3">Pelanggan</th><th class="p-3">Layanan</th><th class="p-3">Produk</th><th class="p-3">Metode</th><th class="p-3">Status</th><th class="p-3">Bayar</th><th class="p-3 text-right">Total</th></tr>
        </thead>
        <tbody>
        @forelse($bookings as $b)
            <tr class="border-t border-stone-100 align-top">
                <td class="p-3 font-semibold">{{ $b->code }}</td>
                <td class="p-3 whitespace-nowrap">{{ tgl($b->slot->date) }}<br><span class="text-stone-500">{{ jam($b->slot->start_time) }}</span></td>
                <td class="p-3">{{ $b->user->name }}</td>
                <td class="p-3">{{ $b->bookingServices->pluck('service.name')->implode(', ') }}</td>
                <td class="p-3">{{ $b->bookingProducts->map(fn ($p) => $p->product->name . ' x' . $p->qty)->implode(', ') ?: '-' }}</td>
                <td class="p-3">{{ $b->payment->method === 'midtrans' ? 'Online' : ($b->payment->method === 'cod' ? 'COD' : '-') }}</td>
                <td class="p-3">{{ $b->status_label }}</td>
                <td class="p-3">{{ $b->payment->status_label }}</td>
                <td class="p-3 text-right font-semibold whitespace-nowrap">{{ rupiah($b->total) }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="p-6 text-center text-stone-500">Tidak ada data pada periode/filter ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="no-print mt-4">{{ $bookings->links() }}</div>
@endsection
