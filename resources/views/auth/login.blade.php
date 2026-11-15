@extends('layouts.landing')
@section('title', 'Login')
@section('bg', 'images/login.jpg')
@section('overlay', 'bg-black/20')
@section('content')
<div class="min-h-[calc(100vh-72px)] flex items-center justify-center lg:justify-end py-10">
    <div class="w-full max-w-md bg-black/85 backdrop-blur border border-gold/30 rounded-2xl p-8 shadow-2xl">
        <div class="text-center">
            <h1 class="font-serif text-3xl font-bold text-gold">KTNG CUT 23</h1>
            <p class="mt-1 text-sm text-white/85">Selamat Datang Kembali</p>
            <div class="mx-auto mt-3 h-1 w-16 rounded bg-gold"></div>
        </div>

        <h2 class="mt-8 text-xl font-semibold">Login Akun</h2>
        <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Masukkan email Anda"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <label class="flex items-center gap-2 text-sm text-white/80">
                <input type="checkbox" name="remember" value="1" class="accent-[#F2B95C]"> Ingat saya
            </label>
            <button class="w-full rounded-lg bg-gold py-3 font-bold text-black hover:opacity-90">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm text-white/80">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-gold hover:underline">Daftar Sekarang</a>
        </p>
    </div>
</div>
@endsection