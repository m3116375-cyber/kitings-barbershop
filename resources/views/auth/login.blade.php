
@extends('layouts.app')
@section('title', 'Login')
@section('content')

<div class="relative left-1/2 -translate-x-1/2 w-screen min-h-[calc(100vh-4rem)] flex items-center justify-end bg-cover bg-center bg-no-repeat px-5 md:px-12 py-12"
     style="background-image: linear-gradient(90deg, rgba(0,0,0,0.15), rgba(0,0,0,0.35)), url('{{ asset('images/login.jpg') }}');">

    <div class="w-full max-w-md bg-black/80 backdrop-blur-md border border-amber-500/40 rounded-2xl p-8 shadow-2xl">

        <div class="text-center mb-7">
            <h1 class="text-3xl font-bold text-amber-400 tracking-wide">
                KTNG CUT 23
            </h1>
            <p class="text-stone-300 text-sm mt-2">
                Selamat Datang Kembali
            </p>
            <div class="w-16 h-1 bg-amber-400 mx-auto mt-4 rounded-full"></div>
        </div>

        <h2 class="text-xl font-semibold text-white mb-6">
            Login Akun
        </h2>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-amber-300 mb-2">
                    E-mail
                </label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    required autofocus
                    placeholder="Masukkan email Anda"
                    class="w-full bg-white/10 border border-stone-500 text-white placeholder-stone-400 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400">
            </div>

            <div>
                <label class="block text-sm font-semibold text-amber-300 mb-2">
                    Password
                </label>
                <input type="password" name="password"
                    required
                    placeholder="Masukkan password"
                    class="w-full bg-white/10 border border-stone-500 text-white placeholder-stone-400 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400">
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-300">
                <input type="checkbox" name="remember" value="1"
                    class="accent-amber-400">
                Ingat saya
            </label>

            <button type="submit"
                class="w-full bg-amber-500 text-black py-3 rounded-lg font-bold hover:bg-amber-400 transition duration-200">
                Masuk
            </button>
        </form>

        <p class="text-sm text-stone-300 text-center mt-6">
            Belum punya akun?
            <a class="text-amber-400 font-semibold hover:text-amber-300"
               href="{{ route('register') }}">
                Daftar Sekarang
            </a>
        </p>

        <p class="text-xs text-stone-500 text-center mt-6 tracking-widest">
            KTNG CUT 23 · PREMIUM BARBERSHOP
        </p>

    </div>
</div>

@endsection
