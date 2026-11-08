@extends('layouts.panel')
@section('title', 'Kelola Pesanan')
@section('content')
<h1 class="text-2xl font-bold mb-4">Kelola Pesanan</h1>

<form method="GET" class="bg-white rounded-lg border border-stone-200 p-4 mb-4 grid sm:grid-cols-4 gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold mb-1">Status</label>
        <select name="status" class="w-full border border-stone-300 rounded px-2 py-2 text-sm">
            <option value="">Semua</option>
            @foreach($statuses as $key => [$label])
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold mb-1">Tanggal slot</label>
        <input type="date" name="date" value="{{ request('date') }}" class="w-full border border-stone-300 rounded px-2 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold mb-1">Cari kode / nama</label>
        <input type="text" name="q" value="{{ request('q') }}" class="w-full border border-stone-300 rounded px-2 py-2 text-sm">
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 rounded bg-brand text-white text-sm font-semibold">Filter</button>
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded border border-stone-300 text-sm">Reset</a>
    </div>
</form>

<div class="bg-white rounded-lg border border-stone-200 overflow-x-auto">
    @include('admin.orders.table', ['orders' => $orders])
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
