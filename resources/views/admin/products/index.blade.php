@extends('layouts.panel')
@section('title', 'Kelola Produk')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Kelola Produk</h1>
    <a href="{{ route('admin.products.create') }}" class="px-4 py-2 rounded bg-brand text-white text-sm font-semibold">+ Tambah Produk</a>
</div>
<div class="bg-white rounded-lg border border-stone-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-soft text-left"><tr><th class="p-3">Foto</th><th class="p-3">Produk</th><th class="p-3">Harga</th><th class="p-3">Stok</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
        <tbody>
        @forelse($products as $p)
            <tr class="border-t border-stone-100">
                <td class="p-3">@include('partials.product-img', ['product' => $p, 'size' => 'w-12 h-12'])</td>
                <td class="p-3"><div class="font-semibold">{{ $p->name }}</div><div class="text-xs text-stone-500">{{ $p->description }}</div></td>
                <td class="p-3">{{ rupiah($p->price) }}</td>
                <td class="p-3 {{ $p->stock <= 5 ? 'text-red-600 font-bold' : '' }}">{{ $p->stock }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $p->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="p-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.products.edit', $p) }}" class="text-brand font-semibold">Ubah</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')
                        <button class="text-red-600 ml-3">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-6 text-center text-stone-500">Belum ada produk.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
