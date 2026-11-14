
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Beranda') | {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#6B4F45',
                            light: '#AF9C92',
                            soft: '#F1E9E6'
                        }
                    }
                }
            }
        }
    </script>

    @stack('head')
</head>

<body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">

    <!-- NAVBAR FIXED GLOBAL -->
    <header class="fixed top-0 left-0 right-0 z-50
                   w-full bg-black/90 backdrop-blur-md
                   border-b border-white/10 text-white">

        <div class="max-w-6xl mx-auto px-5 py-4 flex items-center justify-between">

            <a href="{{ route('home') }}"
               class="font-bold tracking-wide text-amber-400">
                KITING'S BARBERSHOP
            </a>

            <nav class="flex items-center gap-4 text-sm">

                <a href="{{ route('home') }}"
                   class="hover:text-amber-400">
                    Beranda
                </a>

                <a href="{{ route('catalog') }}"
                   class="hover:text-amber-400">
                    Layanan &amp; Produk
                </a>

                @auth
                    {{-- Menu pengguna --}}
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="hover:text-amber-400">
                        Logout
                    </a>

                    <form id="logout-form"
                          action="{{ route('logout') }}"
                          method="POST"
                          class="hidden">
                        @csrf
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="hover:text-amber-400">
                        Login
                    </a>

                    <a href="{{ route('register') }}"
                       class="hover:text-amber-400">
                        Daftar
                    </a>
                @endauth

            </nav>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 pt-24 pb-6">
        @include('partials.flash')
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-brand text-white/90 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-5 flex flex-col sm:flex-row justify-between gap-2">
            <span>&copy; {{ date('Y') }} Kiting's Barbershop</span>
            <span>
                {{ config('kitings.phone') }} &middot;
                {{ config('kitings.address') }}
            </span>
        </div>
    </footer>

    @stack('scripts')

</body>
</html>
