<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { gold: '#F2B95C', ink: '#140E0A' },
            fontFamily: { serif: ['"Playfair Display"', 'serif'], sans: ['Inter', 'sans-serif'] }
        } } }
    </script>
</head>
<body class="font-sans text-white bg-ink">
<div class="min-h-screen bg-cover bg-center bg-fixed" style="background-image: url('{{ asset(trim($__env->yieldContent('bg')) ?: 'images/hero.jpg') }}')">
  <div class="min-h-screen bg-gradient-to-r from-black/85 via-black/60 to-black/30">

    <header class="border-b border-white/15">
        <div class="max-w-6xl mx-auto px-4 h-[72px] flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-gold font-semibold tracking-wide">
                <i data-lucide="scissors" class="w-7 h-7"></i> KITING'S BARBERSHOP
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="hover:text-gold">Beranda</a>
                <a href="{{ route('catalog') }}" class="hover:text-gold">Layanan &amp; Produk</a>
                @auth
                    @if(auth()->user()->isPelanggan())
                        <a href="{{ route('booking.create') }}" class="hover:text-gold">Booking</a>
                        <a href="{{ route('orders.index') }}" class="hover:text-gold">Pesanan Saya</a>
                    @else
                        <a href="{{ auth()->user()->homeUrl() }}" class="hover:text-gold">Panel {{ ucfirst(auth()->user()->role) }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="px-4 py-1.5 rounded-full border border-gold text-gold hover:bg-gold hover:text-black">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-full border border-white/70 hover:border-gold hover:text-gold">Login</a>
                    <a href="{{ route('register') }}" class="px-4 py-1.5 rounded-full bg-gold text-black font-semibold hover:opacity-90">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 pb-12">
        @include('partials.flash')
        @yield('content')
    </main>
  </div>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>