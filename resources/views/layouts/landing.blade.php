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
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#F2B95C',
                        ink: '#140E0A'
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>

    {{-- ANIMASI CUSTOMER --}}
    <style>
        /* Transisi tombol */
        .customer-site main button,
        .customer-site main a[class*="rounded"][class*="bg-"],
        .customer-site main a[class*="rounded"][class*="border-"] {
            transition:
                transform 0.2s ease,
                background-color 0.2s ease,
                color 0.2s ease,
                box-shadow 0.2s ease;
        }

        /* Efek hover tombol */
        @media (hover: hover) {

            .customer-site main button:hover:not(:disabled),
            .customer-site main a[class*="rounded"][class*="bg-"]:hover,
            .customer-site main a[class*="rounded"][class*="border-"]:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(242, 185, 92, 0.15);
            }
        }

        /* Efek ketika tombol diklik */
        .customer-site main button:active:not(:disabled),
        .customer-site main a[class*="rounded"][class*="bg-"]:active,
        .customer-site main a[class*="rounded"][class*="border-"]:active {
            transform: scale(0.97);
        }

        /* Transisi card */
        .customer-site main section[class*="bg-black/60"],
        .customer-site main div[class*="bg-black/60"] {
            transition:
                transform 0.25s ease,
                border-color 0.25s ease,
                box-shadow 0.25s ease;
        }

        /* Efek hover card */
        @media (hover: hover) {

            .customer-site main section[class*="bg-black/60"]:hover,
            .customer-site main div[class*="bg-black/60"]:hover {
                transform: translateY(-3px);
                border-color: rgba(242, 185, 92, 0.45);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            }
        }

        /* Menghormati pengaturan reduced motion */
        @media (prefers-reduced-motion: reduce) {

            .customer-site main button,
            .customer-site main a,
            .customer-site main section,
            .customer-site main div {
                transition: none !important;
                transform: none !important;
            }
        }

        /* ANIMASI MUNCUL SAAT SCROLL */
        .customer-site .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease,
                transform 0.6s ease;
        }

        .customer-site .reveal.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .customer-site .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>

    @vite('resources/css/animations.css')

    @stack('head')
</head>

<body class="customer-site font-sans text-white bg-ink overflow-x-hidden">

    <div class="min-h-screen bg-cover bg-center bg-fixed"
        style="background-image: url('{{ asset(trim($__env->yieldContent('bg')) ?: 'images/hero.jpg') }}')">

        <div class="min-h-screen @yield('overlay', 'bg-gradient-to-r from-black/85 via-black/60 to-black/30')">

            {{-- NAVBAR --}}
            <header data-site-header class="sticky top-0 z-50 bg-black/70 backdrop-blur border-b border-white/15">

                <div class="max-w-6xl mx-auto px-4 h-[72px] flex items-center justify-between gap-4">

                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3 text-gold font-semibold tracking-wide">

                        <i data-lucide="scissors" class="w-7 h-7"></i>
                        KITING'S BARBERSHOP

                    </a>

                    <nav class="flex items-center gap-4 text-sm">

                        <a href="{{ route('home') }}" class="hover:text-gold">
                            Beranda
                        </a>

                        <a href="{{ route('catalog') }}" class="hover:text-gold">
                            Layanan &amp; Produk
                        </a>

                        @auth

                        @if(auth()->user()->isPelanggan())

                        <a href="{{ route('booking.create') }}" class="hover:text-gold">
                            Booking
                        </a>

                        <a href="{{ route('orders.index') }}" class="hover:text-gold">
                            Pesanan Saya
                        </a>

                        @else

                        <a href="{{ auth()->user()->homeUrl() }}" class="hover:text-gold">
                            Panel {{ ucfirst(auth()->user()->role) }}
                        </a>

                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="px-4 py-1.5 rounded-full border border-gold text-gold hover:bg-gold hover:text-black">
                                Keluar
                            </button>
                        </form>

                        @else

                        <a href="{{ route('login') }}"
                            class="px-4 py-1.5 rounded-full border border-white/70 hover:border-gold hover:text-gold">
                            Login
                        </a>

                        <a href="{{ route('register') }}"
                            class="px-4 py-1.5 rounded-full bg-gold text-black font-semibold hover:opacity-90">
                            Daftar
                        </a>

                        @endauth

                    </nav>

                </div>
            </header>

            {{-- KONTEN --}}
            <main class="max-w-6xl mx-auto px-4 pb-12">

                @include('partials.flash')

                @yield('content')

            </main>

        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>


    @vite('resources/js/animations.js')

    @stack('scripts')

</body>

</html>