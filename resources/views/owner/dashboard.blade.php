@extends('layouts.panel')
@section('title', 'Dashboard Owner')
@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Dashboard Owner</h1>
    <form method="GET" class="flex items-end gap-2 text-sm">
        <div><label class="block text-xs font-semibold">Dari</label><input type="date" name="from" value="{{ $from }}" class="border border-stone-300 rounded px-2 py-1.5"></div>
        <div><label class="block text-xs font-semibold">Sampai</label><input type="date" name="to" value="{{ $to }}" class="border border-stone-300 rounded px-2 py-1.5"></div>
        <button class="px-4 py-1.5 rounded bg-brand text-white font-semibold">Terapkan</button>
    </form>
</div>
<p class="text-sm text-stone-500 mb-4">Periode: {{ tgl($from) }} &ndash; {{ tgl($to) }}</p>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg border border-stone-200 p-4"><div class="text-xs text-stone-500">Total Booking</div><div class="text-2xl font-bold text-brand mt-1">{{ $summary['count'] }}</div></div>
    <div class="bg-white rounded-lg border border-stone-200 p-4"><div class="text-xs text-stone-500">Pendapatan (lunas)</div><div class="text-2xl font-bold text-brand mt-1">{{ rupiah($summary['revenue']) }}</div></div>
    <div class="bg-white rounded-lg border border-stone-200 p-4"><div class="text-xs text-stone-500">Layanan Terlaris</div><div class="text-lg font-bold text-brand mt-1">{{ $topServices->first()->name ?? '-' }}</div></div>
    <div class="bg-white rounded-lg border border-stone-200 p-4"><div class="text-xs text-stone-500">Produk Terlaris</div><div class="text-lg font-bold text-brand mt-1">{{ $topProducts->first()->name ?? '-' }}</div></div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-lg border border-stone-200 p-4">
        <h2 class="font-bold mb-3">Pendapatan per Minggu (8 minggu terakhir)</h2>
        <canvas id="revChart" height="140" data-labels="{{ json_encode($weekly['labels']) }}" data-values="{{ json_encode($weekly['values']) }}"></canvas>
    </section>
    <section class="bg-white rounded-lg border border-stone-200 p-4">
        <h2 class="font-bold mb-3">Keterisian Slot per Hari (%)</h2>
        <canvas id="occChart" height="140" data-labels="{{ json_encode($occupancy['labels']) }}" data-values="{{ json_encode($occupancy['values']) }}"></canvas>
    </section>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <section class="bg-white rounded-lg border border-stone-200 p-4">
        <h2 class="font-bold mb-2">Layanan Terlaris</h2>
        <table class="w-full text-sm">
            @forelse($topServices as $i => $r)
                <tr class="border-t border-stone-100"><td class="py-1.5">{{ $i + 1 }}. {{ $r->name }}</td><td class="py-1.5 text-right font-semibold">{{ $r->total }}x</td></tr>
            @empty
                <tr><td class="text-stone-500 py-2">Belum ada data.</td></tr>
            @endforelse
        </table>
    </section>
    <section class="bg-white rounded-lg border border-stone-200 p-4">
        <h2 class="font-bold mb-2">Produk Terlaris</h2>
        <table class="w-full text-sm">
            @forelse($topProducts as $i => $r)
                <tr class="border-t border-stone-100"><td class="py-1.5">{{ $i + 1 }}. {{ $r->name }}</td><td class="py-1.5 text-right font-semibold">{{ $r->total }} pcs</td></tr>
            @empty
                <tr><td class="text-stone-500 py-2">Belum ada data.</td></tr>
            @endforelse
        </table>
    </section>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    function draw(id, type, label, extra) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        new Chart(el, {
            type: type,
            data: { labels: JSON.parse(el.dataset.labels), datasets: [{ label: label, data: JSON.parse(el.dataset.values), backgroundColor: '#B89A8E', borderColor: '#6B4F45', borderWidth: 1 }] },
            options: Object.assign({ plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }, extra || {})
        });
    }
    draw('revChart', 'bar', 'Pendapatan (Rp)');
    draw('occChart', 'bar', 'Keterisian (%)', { scales: { y: { beginAtZero: true, max: 100 } } });
})();
</script>
@endverbatim
@endpush
