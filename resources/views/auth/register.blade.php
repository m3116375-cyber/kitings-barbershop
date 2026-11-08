@extends('layouts.app')
@section('title', 'Registrasi')
@section('content')
<div class="max-w-md mx-auto bg-white border border-stone-200 rounded-lg p-6">
    <h1 class="text-xl font-bold mb-4">Registrasi Pelanggan</h1>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">Nama lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">No. HP</label>
            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Password (min. 8 karakter)</label>
            <input type="password" name="password" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Ulangi password</label>
            <input type="password" name="password_confirmation" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <button class="w-full bg-brand text-white py-2 rounded font-semibold hover:opacity-90">Daftar</button>
    </form>
    <p class="text-sm text-stone-500 mt-4">Sudah punya akun? <a class="text-brand font-semibold" href="{{ route('login') }}">Login</a></p>
</div>
@endsection
