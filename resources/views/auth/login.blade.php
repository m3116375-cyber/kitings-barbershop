@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="max-w-md mx-auto bg-white border border-stone-200 rounded-lg p-6">
    <h1 class="text-xl font-bold mb-4">Login</h1>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Password</label>
            <input type="password" name="password" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button class="w-full bg-brand text-white py-2 rounded font-semibold hover:opacity-90">Masuk</button>
    </form>
    <p class="text-sm text-stone-500 mt-4">Belum punya akun? <a class="text-brand font-semibold" href="{{ route('register') }}">Daftar</a></p>
</div>
@endsection
