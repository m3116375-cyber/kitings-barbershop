@extends('layouts.landing')
@section('title', 'Booking')
@section('bg', 'images/hero.jpg')
@section('overlay', 'bg-black/75')
@section('content')
<section class="pt-10 pb-6">
    <h1 class="font-serif font-bold text-3xl md:text-4xl">Booking <span class="text-gold">Layanan</span></h1>
    <p class="mt-2 text-white/75">Pilih layanan, tanggal, dan slot waktu. Produk perawatan bisa ditambahkan sekalian.</p>
</section>

<form method="POST" action="{{ route('booking.store') }}" id="booking-app"
      data-slots-url="{{ route('slots.json') }}" data-old-slot="{{ old('slot_id') }}" class="grid lg:grid-cols-3 gap-6">
    @csrf
    <div class="lg:col-span-2 space-y-6">

        <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">1. Pilih Layanan</h2>
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($services as $s)
                    <label class="flex items-start gap-3 border border-white/15 rounded-lg p-3 cursor-pointer hover:border-gold/60 has-[:checked]:border-gold has-[:checked]:bg-gold/10">
                        <input type="checkbox" name="services[]" value="{{ $s->id }}" data-price="{{ $s->price }}" data-name="{{ $s->name }}"
                               class="mt-1 w-4 h-4 accent-[#F2B95C] service-check" @checked(in_array($s->id, old('services', [])))>
                        <span>
                            <span class="block font-semibold">{{ $s->name }}</span>
                            <span class="block text-xs text-white/60">{{ $s->duration_minutes }} menit</span>
                            <span class="block text-gold font-bold">{{ rupiah($s->price) }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">2. Pilih Tanggal &amp; Slot Waktu</h2>
            <input type="date" id="date" name="date" value="{{ old('date') }}" min="{{ $minDate }}" max="{{ $maxDate }}"
                   class="rounded-lg bg-white/10 border border-white/20 px-4 py-2.5 focus:outline-none focus:border-gold [color-scheme:dark]">
            <div id="slots" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                <p class="text-sm text-white/60 col-span-full">Pilih tanggal untuk melihat slot yang tersedia.</p>
            </div>
        </section>

        <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">3. Produk Tambahan <span class="text-sm font-normal text-white/60">(opsional)</span></h2>
            @if($products->isEmpty())
                <p class="text-sm text-white/60">Belum ada produk tersedia.</p>
            @endif
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($products as $p)
                    <div class="flex items-center gap-3 border border-white/15 rounded-lg p-3">
                        @if($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-14 h-14 rounded object-cover shrink-0">
                        @else
                            <div class="w-14 h-14 rounded bg-white/10 flex items-center justify-center text-white/40 shrink-0"><i data-lucide="image" class="w-6 h-6"></i></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm">{{ $p->name }}</div>
                            <div class="text-gold font-bold text-sm">{{ rupiah($p->price) }}</div>
                            <div class="text-xs text-white/55">Stok {{ $p->stock }}</div>
                        </div>
                        <input type="number" name="products[{{ $p->id }}]" value="{{ old('products.' . $p->id, 0) }}" min="0" max="{{ min(20, $p->stock) }}"
                               data-price="{{ $p->price }}" data-name="{{ $p->name }}"
                               class="w-16 rounded-lg bg-white/10 border border-white/20 px-2 py-1.5 text-center focus:outline-none focus:border-gold product-qty">
                    </div>
                @endforeach
            </div>
        </section>

        <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-3">Catatan <span class="text-sm font-normal text-white/60">(opsional)</span></h2>
            <textarea name="notes" rows="2" maxlength="500" placeholder="Contoh: model rambut yang diinginkan"
                      class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">{{ old('notes') }}</textarea>
        </section>
    </div>

    <aside class="lg:sticky lg:top-24 h-fit bg-black/80 backdrop-blur border border-gold/30 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold text-gold mb-3">Ringkasan</h2>
        <ul id="summary" class="text-sm space-y-1.5 mb-4"><li class="text-white/60">Belum ada layanan dipilih.</li></ul>
        <div class="border-t border-white/15 pt-3 flex justify-between font-bold">
            <span>Total</span><span id="total" class="text-gold">Rp 0</span>
        </div>
        <button class="mt-5 w-full rounded-lg bg-gold py-3 font-bold text-black hover:opacity-90">Lanjut ke Pembayaran</button>
        <p class="text-xs text-white/55 mt-3">Setelah booking dibuat, Anda dapat memilih bayar online atau COD.</p>
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
            html += '<li class="flex justify-between gap-3"><span>' + esc(c.dataset.name) + '</span><span>' + fmt(c.dataset.price) + '</span></li>';
        });
        document.querySelectorAll('.product-qty').forEach(i => {
            const q = Number(i.value) || 0;
            if (q > 0) {
                total += q * Number(i.dataset.price);
                html += '<li class="flex justify-between gap-3"><span>' + esc(i.dataset.name) + ' x' + q + '</span><span>' + fmt(q * i.dataset.price) + '</span></li>';
            }
        });
        list.innerHTML = html || '<li class="text-white/60">Belum ada layanan dipilih.</li>';
        document.getElementById('total').textContent = fmt(total);
    }

    function render(data) {
        box.innerHTML = '';
        if (!data.slots.length) {
            box.innerHTML = '<p class="text-sm text-white/60 col-span-full">' + esc(data.message || 'Tidak ada slot.') + '</p>';
            return;
        }
        data.slots.forEach(s => {
            const label = document.createElement('label');
            label.className = 'block ' + (s.available ? 'cursor-pointer' : 'cursor-not-allowed');
            label.innerHTML =
                '<input type="radio" name="slot_id" value="' + s.id + '" class="peer sr-only" ' + (s.available ? '' : 'disabled') +
                (String(oldSlot) === String(s.id) ? ' checked' : '') + '>' +
                '<div class="rounded-lg border px-3 py-2 text-center text-sm ' +
                (s.available
                    ? 'border-white/25 hover:border-gold peer-checked:bg-[#F2B95C] peer-checked:text-black peer-checked:border-[#F2B95C]'
                    : 'border-white/10 bg-white/5 text-white/35') + '">' +
                '<div class="font-semibold">' + esc(s.label) + '</div>' +
                '<div class="text-xs opacity-80">' + esc(s.note) + '</div></div>';
            box.appendChild(label);
        });
    }

    async function loadSlots() {
        if (!dateInput.value) {
            box.innerHTML = '<p class="text-sm text-white/60 col-span-full">Pilih tanggal untuk melihat slot yang tersedia.</p>';
            return;
        }
        box.innerHTML = '<p class="text-sm text-white/60 col-span-full">Memuat slot...</p>';
        try {
            const res = await fetch(app.dataset.slotsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } });
            render(await res.json());
        } catch (e) {
            box.innerHTML = '<p class="text-sm text-red-300 col-span-full">Gagal memuat slot. Coba lagi.</p>';
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