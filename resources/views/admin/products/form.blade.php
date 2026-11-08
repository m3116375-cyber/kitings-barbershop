@extends('layouts.panel')
@section('title', $product->exists ? 'Ubah Produk' : 'Tambah Produk')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $product->exists ? 'Ubah Produk' : 'Tambah Produk' }}</h1>
<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="bg-white border border-stone-200 rounded-lg p-4 max-w-xl space-y-4">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div>
        <label class="block text-sm font-semibold mb-1">Nama produk</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full border border-stone-300 rounded px-3 py-2">{{ old('description', $product->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Harga (Rp)</label>
            <input type="number" name="price" min="0" value="{{ old('price', $product->price) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Stok</label>
            <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required class="w-full border border-stone-300 rounded px-3 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Foto produk (JPG/PNG/WEBP, maks 2 MB)</label>
        @if($product->image_url)<img src="{{ $product->image_url }}" class="w-24 h-24 object-cover rounded mb-2" alt="">@endif
        <input type="file" name="image" accept="image/*" class="text-sm">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Aktif (tampil untuk pelanggan)</label>
    <div class="flex gap-2">
        <button class="px-5 py-2 rounded bg-brand text-white font-semibold">Simpan</button>
        <a href="{{ route('admin.products.index') }}" class="px-5 py-2 rounded border border-stone-300">Batal</a>
    </div>
</form>
@endsection
