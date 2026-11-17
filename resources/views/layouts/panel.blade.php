<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') | {{ config('app.name') }}</title>

    {{-- Font & warna disamakan dengan landing (layouts/landing.blade.php) --}}
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

    <style>
        /* Form control bertema gelap, sama gaya dengan landing */
        .panel-site input:not([type=checkbox]):not([type=radio]):not([type=hidden]):not([type=file]),
        .panel-site select,
        .panel-site textarea {
            background-color: rgba(0, 0, 0, 0.4);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            color-scheme: dark;
        }
        .panel-site input:focus,
        .panel-site select:focus,
        .panel-site textarea:focus {
            outline: none;
            border-color: #F2B95C;
            box-shadow: 0 0 0 1px #F2B95C;
        }
        .panel-site input[type=checkbox],
        .panel-site input[type=radio] { accent-color: #F2B95C; }
        .panel-site select option { background: #140E0A; color: #fff; }
        .panel-site input[type=file]::file-selector-button {
            margin-right: .75rem; padding: .35rem .9rem; border-radius: 9999px;
            border: 1px solid rgba(242, 185, 92, .7); background: transparent; color: #F2B95C; cursor: pointer;
        }

        /* Transisi card & tombol seperti landing */
        .panel-site main button,
        .panel-site main a[class*="rounded"] {
            transition: transform .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease, opacity .2s ease;
        }
        @media (hover: hover) {
            .panel-site main button:hover:not(:disabled) { transform: translateY(-1px); }
        }
        .panel-site main button:active:not(:disabled) { transform: scale(.97); }
        @media (prefers-reduced-motion: reduce) {
            .panel-site * { transition: none !important; transform: none !important; }
        }

        /* Pagination bawaan Laravel */
        .panel-site nav[role=navigation] { color: rgba(255,255,255,.7); }
        .panel-site nav[role=navigation] a,
        .panel-site nav[role=navigation] span[aria-current] > span,
        .panel-site nav[role=navigation] span[aria-disabled] > span {
            background-color: rgba(0,0,0,.4) !important; border-color: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.8) !important;
        }
        .panel-site nav[role=navigation] span[aria-current] > span { color: #F2B95C !important; border-color: #F2B95C !important; }
        .panel-site nav[role=navigation] p { color: rgba(255,255,255,.6) !important; }


        /* ===== Penyesuaian otomatis untuk view lama (tanpa edit file view) ===== */
        .panel-site .bg-white { background-color: rgba(0,0,0,.4) !important; color: #fff; border-radius: .75rem; }
        .panel-site .border-stone-200 { border-color: rgba(255,255,255,.15) !important; }
        .panel-site .border-stone-100 { border-color: rgba(255,255,255,.10) !important; }
        .panel-site .border-stone-300 { border-color: rgba(255,255,255,.20) !important; }
        .panel-site .text-stone-500 { color: rgba(255,255,255,.60) !important; }
        .panel-site .text-stone-600 { color: rgba(255,255,255,.70) !important; }
        .panel-site .text-brand, .panel-site .hover\:text-brand:hover { color: #F2B95C !important; }
        .panel-site .bg-brand { background-color: #F2B95C !important; color: #000 !important; border-radius: 9999px; }
        .panel-site .bg-brand.text-white { color: #000 !important; }
        .panel-site .border-brand { border-color: rgba(242,185,92,.7) !important; border-radius: 9999px; }
        .panel-site .bg-brand-soft { background-color: rgba(255,255,255,.05) !important; color: #F2B95C; }
        .panel-site .hover\:bg-brand-soft:hover { background-color: rgba(242,185,92,.10) !important; }
        .panel-site .bg-stone-200 { background-color: rgba(255,255,255,.10) !important; }
        .panel-site .text-red-600 { color: #f87171 !important; }
        .panel-site h1, .panel-site h2 { font-family: "Playfair Display", serif; }
        .panel-site h1 { color: #fff; }
        .panel-site thead { color: #F2B95C; }

        /* Cetak / simpan PDF: balik ke putih agar hemat tinta */
        @media print {
            .no-print { display: none !important; }
            body.panel-site { background: #fff !important; color: #000 !important; }
            .panel-site main, .panel-site main * {
                background: transparent !important; color: #000 !important; border-color: #ccc !important; box-shadow: none !important;
            }
        }
    </style>
    @stack('head')
</head>
@php
    $role = auth()->user()->role;
    $menu = $role === 'admin'
        ? [
            ['Dashboard', 'admin.dashboard', route('admin.dashboard'), 'layout-dashboard'],
            ['Kelola Pesanan', 'admin.orders.*', route('admin.orders.index'), 'clipboard-list'],
            ['Kelola Layanan', 'admin.services.*', route('admin.services.index'), 'scissors'],
            ['Kelola Produk', 'admin.products.*', route('admin.products.index'), 'package'],
            ['Jam &amp; Slot', 'admin.schedule*', route('admin.schedule'), 'clock'],
        ]
        : [
            ['Dashboard', 'owner.dashboard', route('owner.dashboard'), 'layout-dashboard'],
            ['Laporan', 'owner.reports*', route('owner.reports'), 'chart-column'],
        ];
@endphp
<body class="panel-site font-sans text-white bg-ink min-h-screen">
<div class="flex flex-col md:flex-row min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="no-print md:w-60 bg-black/70 backdrop-blur border-b md:border-b-0 md:border-r border-white/15 md:min-h-screen shrink-0 md:sticky md:top-0 md:h-screen md:overflow-y-auto">
        <div class="px-4 py-5 border-b border-white/15">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-gold font-semibold tracking-wide">
                <i data-lucide="scissors" class="w-6 h-6"></i>
                KITING'S
            </a>
            <div class="mt-2 text-xs text-white/60">{{ ucfirst($role) }} &middot; {{ auth()->user()->name }}</div>
        </div>
        <nav class="p-3 flex md:flex-col gap-1 text-sm overflow-x-auto">
            @foreach($menu as [$label, $pattern, $url, $icon])
                @php $active = request()->routeIs($pattern); @endphp
                <a href="{{ $url }}" class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap {{ $active ? 'bg-gold text-black font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-gold' }}">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>{!! $label !!}
                </a>
            @endforeach

            <div class="hidden md:block my-2 border-t border-white/15"></div>

            <a href="{{ route('home') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg whitespace-nowrap text-white/80 hover:bg-white/10 hover:text-gold">
                <i data-lucide="globe" class="w-4 h-4"></i>Lihat Website
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="w-full flex items-center gap-2 text-left px-3 py-2 rounded-lg whitespace-nowrap text-white/80 hover:bg-white/10 hover:text-gold">
                    <i data-lucide="log-out" class="w-4 h-4"></i>Keluar
                </button>
            </form>
        </nav>
    </aside>

    {{-- KONTEN --}}
    <main class="flex-1 p-4 md:p-8 min-w-0 bg-gradient-to-br from-ink via-ink to-black">
        <div class="no-print">@include('partials.flash')</div>
        @yield('content')
    </main>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>