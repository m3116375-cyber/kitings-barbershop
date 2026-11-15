
@extends('layouts.landing')
@section('title', 'Detail Pesanan')
@section('bg', 'images/hero.jpg')
@section('overlay', 'bg-black/75')

@section('content')
@php $p = $booking->payment; @endphp

{{-- Judul halaman --}}
<section class="pt-10 pb-6">
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="font-serif font-bold text-3xl md:text-4xl">
            Detail <span class="text-gold">Pesanan</span>
        </h1>

        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $booking->status_class }}">
            {{ $booking->status_label }}
        </span>
    </div>

    <p class="mt-2 text-white/75">
        Nomor Pesanan:
        <span class="font-semibold text-gold">{{ $booking->code }}</span>
    </p>

    <a href="{{ route('orders.index') }}"
       class="inline-block mt-3 text-sm text-white/60 hover:text-gold">
        &larr; Kembali ke Riwayat Pesanan
    </a>
</section>

<div id="order-app"
     data-check-url="{{ route('orders.check', $booking) }}"
     data-csrf="{{ csrf_token() }}"
     class="grid md:grid-cols-2 gap-6">

    {{-- Rincian Pesanan --}}
    <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">
            Rincian Pesanan
        </h2>

        @include('partials.order-summary', ['booking' => $booking])

        @if($booking->notes)
            <div class="mt-4 pt-3 border-t border-white/10">
                <p class="text-sm text-white/60 mb-1">Catatan Pesanan</p>
                <p class="text-sm text-white">{{ $booking->notes }}</p>
            </div>
        @endif
    </section>

    {{-- Informasi Pembayaran --}}
    <section class="reveal bg-black/60 backdrop-blur border border-white/15 rounded-xl p-5">
        <h2 class="font-serif text-xl font-semibold border-l-4 border-gold pl-3 mb-4">
            Informasi Pembayaran
        </h2>

        <div class="text-sm space-y-3">
            <div class="flex justify-between gap-3">
                <span class="text-white/60">Metode Pembayaran</span>
                <span class="font-semibold text-right">
                    {{ $p->method_label }}
                </span>
            </div>

            <div class="flex justify-between gap-3 items-center">
                <span class="text-white/60">Status Pembayaran</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $p->status_class }}">
                    {{ $p->status_label }}
                </span>
            </div>

            @if($p->paid_at)
                <div class="flex justify-between gap-3">
                    <span class="text-white/60">Tanggal Pembayaran</span>
                    <span class="text-right">
                        {{ tgl($p->paid_at, 'd M Y H:i') }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Aksi Pembayaran --}}
        @if($booking->isActive() && ! $p->isPaid())
            <div class="mt-5 space-y-3">

                <a href="{{ route('orders.pay', $booking) }}"
                   class="block text-center w-full rounded-lg bg-gold py-3 font-bold text-black hover:opacity-90 transition">
                    {{ $p->method ? 'Ganti / Lanjutkan Pembayaran' : 'Pilih Metode Pembayaran' }}
                </a>

                @if($p->method === 'midtrans')
                    <button type="button"
                            id="btn-check"
                            class="w-full rounded-lg border border-gold py-3 font-bold text-gold hover:bg-gold hover:text-black transition">
                        Cek Status Pembayaran
                    </button>
                @endif
            </div>

            @if($p->method === 'cod')
                <div class="mt-4 rounded-lg bg-yellow-500/10 border border-yellow-400/30 p-3">
                    <p class="text-sm text-yellow-200">
                        Silakan siapkan pembayaran tunai di barbershop setelah layanan selesai.
                    </p>
                </div>
            @endif
        @endif

        {{-- Informasi Pembatalan --}}
        @if($booking->canCustomerCancel())
            <form method="POST"
                  action="{{ route('orders.cancel', $booking) }}"
                  class="mt-5"
                  onsubmit="return confirm('Batalkan pesanan ini?')">
                @csrf

                <button type="submit"
                        class="text-sm text-red-400 hover:text-red-300 hover:underline">
                    Batalkan Pesanan
                </button>
            </form>

        @elseif($booking->isActive())
            <p class="text-xs text-white/50 mt-4">
                Pesanan yang sudah dibayar atau sedang dilayani hanya dapat dibatalkan melalui admin barbershop.
            </p>
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
        btn.disabled = true;
        btn.textContent = 'Memeriksa...';

        try {
            await fetch(app.dataset.checkUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': app.dataset.csrf,
                    'Accept': 'application/json'
                }
            });
        } catch (e) {}

        window.location.reload();
    });
})();
</script>
@endverbatim
@endpush
