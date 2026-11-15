@extends('layouts.landing')
@section('title', 'Pembayaran')
@section('bg', 'images/hero.jpg')
@section('overlay', 'bg-black/75')
@section('content')
<section class="pt-10 pb-6">
    <h1 class="font-serif font-bold text-3xl md:text-4xl">Pem<span class="text-gold">bayaran</span></h1>
    <p class="mt-2 text-white/75">Pesanan <span class="font-semibold text-gold">{{ $booking->code }}</span> berhasil dibuat. Pilih metode pembayaran.</p>
</section>

<div id="pay-app" data-snap-url="{{ route('orders.snap', $booking) }}" data-check-url="{{ route('orders.check', $booking) }}"
     data-show-url="{{ route('orders.show', $booking) }}" data-csrf="{{ csrf_token() }}" class="grid md:grid-cols-2 gap-6">

    {{-- Ringkasan --}}
    <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">Ringkasan Pesanan</h2>
        <div class="text-sm space-y-1.5">
            <div class="flex justify-between"><span class="text-white/60">Tanggal</span><span class="font-semibold">{{ tgl($booking->slot->date, 'l, d F Y') }}</span></div>
            <div class="flex justify-between"><span class="text-white/60">Slot</span><span class="font-semibold">{{ $booking->slot->label }}</span></div>
        </div>
        <table class="w-full text-sm mt-4">
            @foreach($booking->bookingServices as $row)
                <tr class="border-t border-white/10"><td class="py-2">{{ $row->service->name }}</td><td class="py-2 text-right">{{ rupiah($row->price) }}</td></tr>
            @endforeach
            @foreach($booking->bookingProducts as $row)
                <tr class="border-t border-white/10"><td class="py-2">{{ $row->product->name }} x{{ $row->qty }}</td><td class="py-2 text-right">{{ rupiah($row->price * $row->qty) }}</td></tr>
            @endforeach
            <tr class="border-t border-white/25 font-bold"><td class="py-3">Total</td><td class="py-3 text-right text-gold text-lg">{{ rupiah($booking->total) }}</td></tr>
        </table>
    </section>

    {{-- Metode pembayaran --}}
    <section class="space-y-4">
        <div class="bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <i data-lucide="credit-card" class="w-7 h-7 text-gold"></i>
                <div class="font-semibold text-lg">Bayar Online (Midtrans)</div>
            </div>
            <p class="text-sm text-white/65 mt-2">Transfer bank, e-wallet (GoPay, OVO, dll), QRIS, atau kartu.</p>
            @if($midtrans->isConfigured())
                <button type="button" id="btn-online" class="mt-4 w-full rounded-lg bg-gold py-3 font-bold text-black hover:opacity-90 disabled:opacity-60">Bayar Sekarang</button>
            @else
                <div class="mt-4 text-sm rounded-lg bg-yellow-500/15 border border-yellow-400/40 text-yellow-200 p-3">
                    Payment gateway belum dikonfigurasi (admin perlu mengisi key Midtrans di file <code>.env</code>). Sementara itu Anda dapat memilih COD.
                </div>
            @endif
        </div>

        <div class="bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <i data-lucide="banknote" class="w-7 h-7 text-gold"></i>
                <div class="font-semibold text-lg">COD (Bayar di Tempat)</div>
            </div>
            <p class="text-sm text-white/65 mt-2">Bayar langsung di barbershop setelah layanan selesai.</p>
            <form method="POST" action="{{ route('orders.cod', $booking) }}">@csrf
                <button class="mt-4 w-full rounded-lg border border-gold py-3 font-bold text-gold hover:bg-gold hover:text-black">Pilih COD</button>
            </form>
        </div>

        <a href="{{ route('orders.show', $booking) }}" class="block text-center text-sm text-white/60 hover:text-gold">Bayar nanti &rarr; lihat detail pesanan</a>
    </section>
</div>
@endsection

@push('scripts')
@if($midtrans->isConfigured())
    <script src="{{ $midtrans->snapJsUrl() }}" data-client-key="{{ $midtrans->clientKey() }}"></script>
@endif
@verbatim
<script>
(function () {
    const app = document.getElementById('pay-app');
    const btn = document.getElementById('btn-online');
    if (!btn) return;
    const headers = { 'X-CSRF-TOKEN': app.dataset.csrf, 'Accept': 'application/json' };

    function reset() { btn.disabled = false; btn.textContent = 'Bayar Sekarang'; }

    async function done() {
        try { await fetch(app.dataset.checkUrl, { method: 'POST', headers: headers }); } catch (e) {}
        window.location.href = app.dataset.showUrl;
    }

    btn.addEventListener('click', async function () {
        btn.disabled = true; btn.textContent = 'Memproses...';
        try {
            const res = await fetch(app.dataset.snapUrl, { method: 'POST', headers: headers });
            const data = await res.json();
            if (!res.ok || !data.token) throw new Error(data.error || 'Gagal membuat transaksi.');
            window.snap.pay(data.token, {
                onSuccess: done,
                onPending: done,
                onError: function () { alert('Pembayaran gagal. Silakan coba lagi.'); reset(); },
                onClose: reset
            });
        } catch (e) {
            alert(e.message);
            reset();
        }
    });
})();
</script>
@endverbatim
@endpush