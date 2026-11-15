@extends('layouts.landing')
@section('title', 'Registrasi')
@section('bg', 'images/login.jpg')
@section('overlay', 'bg-black/20')
@section('content')
<div class="min-h-[calc(100vh-72px)] flex items-center justify-center lg:justify-end py-10">
    <div class="w-full max-w-md bg-black/85 backdrop-blur border border-gold/30 rounded-2xl p-8 shadow-2xl">
        <div class="text-center">
            <h1 class="font-serif text-3xl font-bold text-gold">KTNG CUT 23</h1>
            <p class="mt-1 text-sm text-white/85">Buat Akun Baru</p>
            <div class="mx-auto mt-3 h-1 w-16 rounded bg-gold"></div>
        </div>

        <h2 class="mt-8 text-xl font-semibold">Registrasi Pelanggan</h2>
        <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">Nama lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama lengkap"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email Anda"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">No. HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">Password <span class="font-normal text-white/60">(min. 8 karakter)</span></label>
                <input type="password" name="password" required placeholder="Masukkan password"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gold mb-1">Ulangi password</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi password"
                       class="w-full rounded-lg bg-white/10 border border-white/20 px-4 py-3 placeholder-white/50 focus:outline-none focus:border-gold">
            </div>
            <button class="w-full rounded-lg bg-gold py-3 font-bold text-black hover:opacity-90">Daftar</button>
        </form>

        <p class="mt-6 text-center text-sm text-white/80">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-gold hover:underline">Login di sini</a>
        </p>
    </div>
</div>
@endsection