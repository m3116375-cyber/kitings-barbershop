@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('content')
@php $p = $booking->payment; @endphp
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('orders.index') }}" class="text-sm text-stone-500 hover:text-brand">&larr; Riwayat</a>
    <h1 class="text-2xl font-bold">Pesanan {{ $booking->code }}</h1>
    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $booking->status_class }}">{{ $booking->status_label }}</span>
</div>

<div id="order-app" data-check-url="{{ route('orders.check', $booking) }}" data-csrf="{{ csrf_token() }}" class="grid md:grid-cols-2 gap-6">
    <section class="bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-3">Rincian</h2>
        @include('partials.order-summary', ['booking' => $booking])
        @if($booking->notes)<p class="text-sm mt-3 text-stone-600"><span class="font-semibold">Catatan:</span> {{ $booking->notes }}</p>@endif
    </section>

    <section class="bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-3">Pembayaran</h2>
        <div class="text-sm space-y-1">
            <div class="flex justify-between"><span class="text-stone-500">Metode</span><span class="font-semibold">{{ $p->method_label }}</span></div>
            <div class="flex justify-between"><span class="text-stone-500">Status</span>
                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $p->status_class }}">{{ $p->status_label }}</span></div>
            @if($p->paid_at)<div class="flex justify-between"><span class="text-stone-500">Dibayar pada</span><span>{{ tgl($p->paid_at, 'd M Y H:i') }}</span></div>@endif
        </div>

        @if($booking->isActive() && ! $p->isPaid())
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('orders.pay', $booking) }}" class="px-4 py-2 rounded bg-brand text-white text-sm font-semibold">
                    {{ $p->method ? 'Ganti / Lanjutkan Pembayaran' : 'Pilih Metode Pembayaran' }}
                </a>
                @if($p->method === 'midtrans')
                    <button type="button" id="btn-check" class="px-4 py-2 rounded border border-brand text-brand text-sm font-semibold">Cek Status Pembayaran</button>
                @endif
            </div>
            @if($p->method === 'cod')
                <p class="text-xs text-stone-500 mt-3">Siapkan pembayaran tunai di barbershop setelah layanan selesai.</p>
            @endif
        @endif

        @if($booking->canCustomerCancel())
            <form method="POST" action="{{ route('orders.cancel', $booking) }}" class="mt-4"
                  onsubmit="return confirm('Batalkan pesanan ini?')">@csrf
                <button class="text-sm text-red-600 hover:underline">Batalkan pesanan</button>
            </form>
        @elseif($booking->isActive())
            <p class="text-xs text-stone-500 mt-4">Pesanan yang sudah dibayar/dilayani hanya dapat dibatalkan melalui admin barbershop.</p>
        @endif
    </section>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    const btn = document.getElementById('btn-check');
    if (!btn) return;
    const app = document.getElementById('order-app');
    btn.addEventListener('click', async function () {
        btn.disabled = true; btn.textContent = 'Memeriksa...';
        try {
            await fetch(app.dataset.checkUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': app.dataset.csrf, 'Accept': 'application/json' } });
        } catch (e) {}
        window.location.reload();
    });
})();
</script>
@endverbatim
@endpush
