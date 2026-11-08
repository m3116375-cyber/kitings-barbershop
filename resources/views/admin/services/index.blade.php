@extends('layouts.panel')
@section('title', 'Kelola Layanan')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Kelola Layanan</h1>
    <a href="{{ route('admin.services.create') }}" class="px-4 py-2 rounded bg-brand text-white text-sm font-semibold">+ Tambah Layanan</a>
</div>
<div class="bg-white rounded-lg border border-stone-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-soft text-left"><tr><th class="p-3">Layanan</th><th class="p-3">Durasi</th><th class="p-3">Harga</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
        <tbody>
        @forelse($services as $s)
            <tr class="border-t border-stone-100">
                <td class="p-3"><div class="font-semibold">{{ $s->name }}</div><div class="text-xs text-stone-500">{{ $s->description }}</div></td>
                <td class="p-3">{{ $s->duration_minutes }} mnt</td>
                <td class="p-3">{{ rupiah($s->price) }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="p-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.services.edit', $s) }}" class="text-brand font-semibold">Ubah</a>
                    <form method="POST" action="{{ route('admin.services.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus layanan ini?')">@csrf @method('DELETE')
                        <button class="text-red-600 ml-3">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="p-6 text-center text-stone-500">Belum ada layanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
