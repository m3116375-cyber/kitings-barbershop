@extends('layouts.app')
@section('title', 'Booking')
@section('content')
<h1 class="text-2xl font-bold mb-4">Booking Layanan</h1>

<form method="POST" action="{{ route('booking.store') }}" id="booking-app"
      data-slots-url="{{ route('slots.json') }}" data-old-slot="{{ old('slot_id') }}" class="grid lg:grid-cols-3 gap-6">
    @csrf
    <div class="lg:col-span-2 space-y-6">
        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">1. Pilih Layanan</h2>
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($services as $s)
                    <label class="flex items-start gap-3 border border-stone-200 rounded p-3 cursor-pointer hover:bg-brand-soft">
                        <input type="checkbox" name="services[]" value="{{ $s->id }}" data-price="{{ $s->price }}" data-name="{{ $s->name }}"
                               class="mt-1 service-check" @checked(in_array($s->id, old('services', [])))>
                        <span>
                            <span class="block font-semibold">{{ $s->name }}</span>
                            <span class="block text-xs text-stone-500">{{ $s->duration_minutes }} menit</span>
                            <span class="block text-brand font-bold">{{ rupiah($s->price) }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">2. Pilih Tanggal &amp; Slot Waktu</h2>
            <input type="date" id="date" name="date" value="{{ old('date') }}" min="{{ $minDate }}" max="{{ $maxDate }}"
                   class="border border-stone-300 rounded px-3 py-2">
            <div id="slots" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                <p class="text-sm text-stone-500 col-span-full">Pilih tanggal untuk melihat slot yang tersedia.</p>
            </div>
        </section>

        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-3">3. Produk Tambahan <span class="text-sm font-normal text-stone-500">(opsional)</span></h2>
            @if($products->isEmpty())
                <p class="text-sm text-stone-500">Belum ada produk tersedia.</p>
            @endif
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($products as $p)
                    <div class="flex items-center gap-3 border border-stone-200 rounded p-3">
                        @include('partials.product-img', ['product' => $p, 'size' => 'w-14 h-14'])
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm">{{ $p->name }}</div>
                            <div class="text-brand font-bold text-sm">{{ rupiah($p->price) }}</div>
                            <div class="text-xs text-stone-500">Stok {{ $p->stock }}</div>
                        </div>
                        <input type="number" name="products[{{ $p->id }}]" value="{{ old('products.' . $p->id, 0) }}" min="0" max="{{ min(20, $p->stock) }}"
                               data-price="{{ $p->price }}" data-name="{{ $p->name }}" class="w-16 border border-stone-300 rounded px-2 py-1 product-qty">
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white border border-stone-200 rounded-lg p-4">
            <h2 class="font-bold mb-2">Catatan <span class="text-sm font-normal text-stone-500">(opsional)</span></h2>
            <textarea name="notes" rows="2" maxlength="500" class="w-full border border-stone-300 rounded px-3 py-2" placeholder="Contoh: model rambut yang diinginkan">{{ old('notes') }}</textarea>
        </section>
    </div>

    <aside class="lg:sticky lg:top-4 h-fit bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-3">Ringkasan</h2>
        <ul id="summary" class="text-sm space-y-1 mb-3"><li class="text-stone-500">Belum ada layanan dipilih.</li></ul>
        <div class="border-t border-stone-200 pt-3 flex justify-between font-bold">
            <span>Total</span><span id="total" class="text-brand">Rp 0</span>
        </div>
        <button class="mt-4 w-full bg-brand text-white py-2.5 rounded font-semibold hover:opacity-90">Lanjut ke Pembayaran</button>
        <p class="text-xs text-stone-500 mt-2">Setelah booking dibuat, Anda dapat memilih bayar online atau COD.</p>
    </aside>
</form>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    const app = document.getElementById('booking-app');
    const dateInput = document.getElementById('date');
    const box = document.getElementById('slots');
    const oldSlot = app.dataset.oldSlot;
    const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');

    function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    function updateSummary() {
        const list = document.getElementById('summary');
        let total = 0, html = '';
        document.querySelectorAll('.service-check:checked').forEach(c => {
            total += Number(c.dataset.price);
            html += '<li class="flex justify-between"><span>' + esc(c.dataset.name) + '</span><span>' + fmt(c.dataset.price) + '</span></li>';
        });
        document.querySelectorAll('.product-qty').forEach(i => {
            const q = Number(i.value) || 0;
            if (q > 0) {
                total += q * Number(i.dataset.price);
                html += '<li class="flex justify-between"><span>' + esc(i.dataset.name) + ' x' + q + '</span><span>' + fmt(q * i.dataset.price) + '</span></li>';
            }
        });
        list.innerHTML = html || '<li class="text-stone-500">Belum ada layanan dipilih.</li>';
        document.getElementById('total').textContent = fmt(total);
    }

    function render(data) {
        box.innerHTML = '';
        if (!data.slots.length) {
            box.innerHTML = '<p class="text-sm text-stone-500 col-span-full">' + esc(data.message || 'Tidak ada slot.') + '</p>';
            return;
        }
        data.slots.forEach(s => {
            const label = document.createElement('label');
            label.className = 'block ' + (s.available ? 'cursor-pointer' : 'cursor-not-allowed opacity-60');
            label.innerHTML =
                '<input type="radio" name="slot_id" value="' + s.id + '" class="peer sr-only" ' + (s.available ? '' : 'disabled') +
                (String(oldSlot) === String(s.id) ? ' checked' : '') + '>' +
                '<div class="border rounded px-3 py-2 text-center text-sm peer-checked:bg-[#6B4F45] peer-checked:text-white peer-checked:border-[#6B4F45] ' +
                (s.available ? 'hover:bg-stone-100' : 'bg-stone-200') + '">' +
                '<div class="font-semibold">' + esc(s.label) + '</div>' +
                '<div class="text-xs ' + (s.available ? 'text-green-700' : 'text-red-600') + '">' + esc(s.note) + '</div></div>';
            box.appendChild(label);
        });
    }

    async function loadSlots() {
        if (!dateInput.value) {
            box.innerHTML = '<p class="text-sm text-stone-500 col-span-full">Pilih tanggal untuk melihat slot yang tersedia.</p>';
            return;
        }
        box.innerHTML = '<p class="text-sm text-stone-500 col-span-full">Memuat slot...</p>';
        try {
            const res = await fetch(app.dataset.slotsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } });
            render(await res.json());
        } catch (e) {
            box.innerHTML = '<p class="text-sm text-red-600 col-span-full">Gagal memuat slot. Coba lagi.</p>';
        }
    }

    dateInput.addEventListener('change', loadSlots);
    document.querySelectorAll('.service-check, .product-qty').forEach(el => el.addEventListener('input', updateSummary));
    updateSummary();
    if (dateInput.value) loadSlots();
})();
</script>
@endverbatim
@endpush
