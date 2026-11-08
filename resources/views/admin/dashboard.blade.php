@extends('layouts.panel')
@section('title', 'Dashboard Admin')
@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Pesanan Baru (hari ini)', $newToday], ['Booking Hari Ini', $bookingsToday], ['Menunggu Konfirmasi', $waiting], ['Stok Menipis (&le; 5)', $lowStock]] as [$label, $value])
        <div class="bg-white rounded-lg border border-stone-200 p-4">
            <div class="text-xs text-stone-500">{!! $label !!}</div>
            <div class="text-3xl font-bold text-brand mt-1">{{ $value }}</div>
        </div>
    @endforeach
</div>

<div class="bg-white rounded-lg border border-stone-200 overflow-x-auto">
    <div class="p-4 flex items-center justify-between">
        <h2 class="font-bold">Pesanan Terbaru</h2>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand font-semibold">Lihat semua</a>
    </div>
    @include('admin.orders.table', ['orders' => $latest])
</div>
@endsection
