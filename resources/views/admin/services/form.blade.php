@extends('layouts.panel')
@section('title', $service->exists ? 'Ubah Layanan' : 'Tambah Layanan')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $service->exists ? 'Ubah Layanan' : 'Tambah Layanan' }}</h1>
<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="bg-white border border-stone-200 rounded-lg p-4 max-w-xl space-y-4">
    @csrf
    @if($service->exists) @method('PUT') @endif
    <div>
        <label class="block text-sm font-semibold mb-1">Nama layanan</label>
        <input type="text" name="name" value="{{ old('name', $service->name) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full border border-stone-300 rounded px-3 py-2">{{ old('description', $service->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Harga (Rp)</label>
            <input type="number" name="price" min="0" value="{{ old('price', $service->price) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Durasi (menit)</label>
            <input type="number" name="duration_minutes" min="5" value="{{ old('duration_minutes', $service->duration_minutes) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Aktif (tampil untuk pelanggan)</label>
    <div class="flex gap-2">
        <button class="px-5 py-2 rounded bg-brand text-white font-semibold">Simpan</button>
        <a href="{{ route('admin.services.index') }}" class="px-5 py-2 rounded border border-stone-300">Batal</a>
    </div>
</form>
@endsection
