@extends('layouts.panel')
@section('title', 'Jadwalkan Ulang')
@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('admin.orders.show', $booking) }}" class="text-sm text-stone-500 hover:text-brand">&larr; Detail pesanan</a>
    <h1 class="text-2xl font-bold">Jadwalkan Ulang {{ $booking->code }}</h1>
</div>
<form method="POST" action="{{ route('admin.orders.reschedule.store', $booking) }}" id="resched-app" data-slots-url="{{ route('slots.json') }}"
      class="bg-white border border-stone-200 rounded-lg p-4 max-w-2xl">
    @csrf
    <p class="text-sm mb-4">Pelanggan: <span class="font-semibold">{{ $booking->user->name }}</span><br>
        Jadwal saat ini: <span class="font-semibold">{{ tgl($booking->slot->date, 'l, d F Y') }}, {{ $booking->slot->label }}</span></p>
    <label class="block text-sm font-semibold mb-1">Tanggal baru</label>
    <input type="date" id="date" min="{{ $minDate }}" max="{{ $maxDate }}" class="border border-stone-300 rounded px-3 py-2">
    <div id="slots" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
    <button class="mt-5 px-5 py-2 rounded bg-brand text-white font-semibold">Simpan Jadwal Baru</button>
</form>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    const app = document.getElementById('resched-app');
    const dateInput = document.getElementById('date');
    const box = document.getElementById('slots');
    function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    dateInput.addEventListener('change', async function () {
        if (!dateInput.value) return;
        box.innerHTML = '<p class="text-sm text-stone-500 col-span-full">Memuat slot...</p>';
        try {
            const res = await fetch(app.dataset.slotsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            box.innerHTML = '';
            if (!data.slots.length) { box.innerHTML = '<p class="text-sm text-stone-500 col-span-full">' + esc(data.message || 'Tidak ada slot.') + '</p>'; return; }
            data.slots.forEach(function (s) {
                const label = document.createElement('label');
                label.className = 'block ' + (s.available ? 'cursor-pointer' : 'cursor-not-allowed opacity-60');
                label.innerHTML = '<input type="radio" name="slot_id" value="' + s.id + '" class="peer sr-only" ' + (s.available ? '' : 'disabled') + '>' +
                    '<div class="border rounded px-3 py-2 text-center text-sm peer-checked:bg-[#6B4F45] peer-checked:text-white ' + (s.available ? '' : 'bg-stone-200') + '">' +
                    '<div class="font-semibold">' + esc(s.label) + '</div><div class="text-xs">' + esc(s.note) + '</div></div>';
                box.appendChild(label);
            });
        } catch (e) { box.innerHTML = '<p class="text-sm text-red-600 col-span-full">Gagal memuat slot.</p>'; }
    });
})();
</script>
@endverbatim
@endpush
