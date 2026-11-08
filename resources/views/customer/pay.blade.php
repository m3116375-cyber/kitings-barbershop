@extends('layouts.app')
@section('title', 'Pembayaran')
@push('head')
    @if($midtrans->isConfigured())
        <script src="{{ $midtrans->snapJsUrl() }}" data-client-key="{{ $midtrans->clientKey() }}"></script>
    @endif
@endpush
@section('content')
<h1 class="text-2xl font-bold mb-1">Pembayaran</h1>
<p class="text-stone-500 mb-5">Pesanan <span class="font-semibold">{{ $booking->code }}</span> berhasil dibuat. Pilih metode pembayaran.</p>

<div id="pay-app" data-snap-url="{{ route('orders.snap', $booking) }}" data-check-url="{{ route('orders.check', $booking) }}"
     data-show-url="{{ route('orders.show', $booking) }}" data-csrf="{{ csrf_token() }}" class="grid md:grid-cols-2 gap-6">

    <section class="bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-3">Ringkasan Pesanan</h2>
        @include('partials.order-summary', ['booking' => $booking])
    </section>

    <section class="space-y-4">
        <div class="bg-white border border-stone-200 rounded-lg p-4">
            <div class="font-bold">Bayar Online (Midtrans)</div>
            <p class="text-sm text-stone-500 mt-1">Transfer bank, e-wallet (GoPay, OVO, dll), QRIS, atau kartu.</p>
            @if($midtrans->isConfigured())
                <button type="button" id="btn-online" class="mt-3 w-full bg-brand text-white py-2.5 rounded font-semibold hover:opacity-90">Bayar Sekarang</button>
            @else
                <div class="mt-3 text-sm rounded bg-yellow-50 border border-yellow-300 text-yellow-800 p-3">
                    Payment gateway belum dikonfigurasi (admin perlu mengisi key Midtrans di file <code>.env</code>). Sementara itu Anda dapat memilih COD.
                </div>
            @endif
        </div>

        <div class="bg-white border border-stone-200 rounded-lg p-4">
            <div class="font-bold">COD (Bayar di Tempat)</div>
            <p class="text-sm text-stone-500 mt-1">Bayar langsung di barbershop setelah layanan selesai.</p>
            <form method="POST" action="{{ route('orders.cod', $booking) }}">@csrf
                <button class="mt-3 w-full border border-brand text-brand py-2.5 rounded font-semibold hover:bg-brand hover:text-white">Pilih COD</button>
            </form>
        </div>

        <a href="{{ route('orders.show', $booking) }}" class="block text-center text-sm text-stone-500 hover:text-brand">Bayar nanti &rarr; lihat detail pesanan</a>
    </section>
</div>
@endsection

@push('scripts')
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
