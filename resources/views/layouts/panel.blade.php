<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT: '#6B4F45', light: '#AF9C92', soft: '#F1E9E6' } } } } }
    </script>
    <style>@media print { .no-print { display: none !important; } body { background: #fff; } }</style>
    @stack('head')
</head>
@php
    $role = auth()->user()->role;
    $menu = $role === 'admin'
        ? [
            ['Dashboard', 'admin.dashboard', route('admin.dashboard')],
            ['Kelola Pesanan', 'admin.orders.*', route('admin.orders.index')],
            ['Kelola Layanan', 'admin.services.*', route('admin.services.index')],
            ['Kelola Produk', 'admin.products.*', route('admin.products.index')],
            ['Jam &amp; Slot', 'admin.schedule*', route('admin.schedule')],
        ]
        : [
            ['Dashboard', 'owner.dashboard', route('owner.dashboard')],
            ['Laporan', 'owner.reports*', route('owner.reports')],
        ];
@endphp
<body class="bg-stone-100 text-stone-800 min-h-screen">
<div class="flex flex-col md:flex-row min-h-screen">
    <aside class="no-print md:w-56 bg-brand text-white md:min-h-screen shrink-0">
        <div class="px-4 py-4 border-b border-white/20">
            <div class="font-bold tracking-wide">KITING'S</div>
            <div class="text-xs text-white/70">{{ ucfirst($role) }} &middot; {{ auth()->user()->name }}</div>
        </div>
        <nav class="p-2 flex md:flex-col gap-1 text-sm overflow-x-auto">
            @foreach($menu as [$label, $pattern, $url])
                <a href="{{ $url }}" class="px-3 py-2 rounded whitespace-nowrap {{ request()->routeIs($pattern) ? 'bg-white text-brand font-semibold' : 'hover:bg-white/10' }}">{!! $label !!}</a>
            @endforeach
            <a href="{{ route('home') }}" class="px-3 py-2 rounded hover:bg-white/10 whitespace-nowrap">Lihat Website</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="w-full text-left px-3 py-2 rounded hover:bg-white/10">Keluar</button>
            </form>
        </nav>
    </aside>
    <main class="flex-1 p-4 md:p-6 min-w-0">
        <div class="no-print">@include('partials.flash')</div>
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
