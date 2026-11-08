<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#6B4F45', light: '#AF9C92', soft: '#F1E9E6' } } } } }
    </script>
    @stack('head')
</head>
<body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">
    <header class="bg-white border-b border-stone-200">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="font-bold tracking-wide text-brand">KITING'S BARBERSHOP</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
                <a href="{{ route('catalog') }}" class="hover:text-brand">Layanan &amp; Produk</a>
                @auth
                    @if(auth()->user()->isPelanggan())
                        <a href="{{ route('booking.create') }}" class="hover:text-brand">Booking</a>
                        <a href="{{ route('orders.index') }}" class="hover:text-brand">Pesanan Saya</a>
                    @else
                        <a href="{{ auth()->user()->homeUrl() }}" class="hover:text-brand">Panel {{ ucfirst(auth()->user()->role) }}</a>
                    @endif
                    <span class="hidden sm:inline text-stone-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="px-3 py-1 rounded border border-brand text-brand hover:bg-brand hover:text-white">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1 rounded border border-brand text-brand hover:bg-brand hover:text-white">Login</a>
                    <a href="{{ route('register') }}" class="px-3 py-1 rounded bg-brand text-white hover:opacity-90">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 py-6">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="bg-brand text-white/90 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-5 flex flex-col sm:flex-row justify-between gap-2">
            <span>&copy; {{ date('Y') }} Kiting's Barbershop</span>
            <span>{{ config('kitings.phone') }} &middot; {{ config('kitings.address') }}</span>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
