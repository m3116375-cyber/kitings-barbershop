@extends('layouts.panel')
@section('title', 'Jam & Slot')
@section('content')
<h1 class="text-2xl font-bold mb-4">Jam Operasional &amp; Kapasitas Slot</h1>

<div class="grid xl:grid-cols-2 gap-6">
    <section class="bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-1">Jam Operasional</h2>
        <p class="text-xs text-stone-500 mb-3">Slot dibuat otomatis dari pengaturan ini saat pelanggan membuka suatu tanggal. Slot yang sudah terbentuk tidak berubah &mdash; ubah per slot di panel kanan.</p>
        <form method="POST" action="{{ route('admin.schedule.hours') }}">
            @csrf @method('PUT')
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-stone-500"><tr><th class="py-1">Hari</th><th>Buka</th><th>Jam buka</th><th>Jam tutup</th><th>Durasi slot</th><th>Kapasitas</th></tr></thead>
                <tbody>
                @foreach($hours as $h)
                    @php $d = $h->day_of_week; @endphp
                    <tr class="border-t border-stone-100">
                        <td class="py-2 font-semibold">{{ $h->day_name }}</td>
                        <td>
                            <input type="hidden" name="hours[{{ $d }}][is_open]" value="0">
                            <input type="checkbox" name="hours[{{ $d }}][is_open]" value="1" @checked(old("hours.$d.is_open", $h->is_open ? '1' : '0') == '1')>
                        </td>
                        <td><input type="time" name="hours[{{ $d }}][open_time]" value="{{ old("hours.$d.open_time", jam($h->open_time)) }}" class="border border-stone-300 rounded px-1 py-1"></td>
                        <td><input type="time" name="hours[{{ $d }}][close_time]" value="{{ old("hours.$d.close_time", jam($h->close_time)) }}" class="border border-stone-300 rounded px-1 py-1"></td>
                        <td><input type="number" name="hours[{{ $d }}][slot_duration]" min="10" max="240" step="5" value="{{ old("hours.$d.slot_duration", $h->slot_duration) }}" class="w-16 border border-stone-300 rounded px-1 py-1"> mnt</td>
                        <td><input type="number" name="hours[{{ $d }}][capacity]" min="1" max="50" value="{{ old("hours.$d.capacity", $h->capacity) }}" class="w-14 border border-stone-300 rounded px-1 py-1"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <button class="mt-4 px-5 py-2 rounded bg-brand text-white font-semibold text-sm">Simpan Pengaturan</button>
        </form>
    </section>

    <section class="bg-white border border-stone-200 rounded-lg p-4">
        <h2 class="font-bold mb-3">Slot pada Tanggal</h2>
        <form method="GET" class="flex gap-2 mb-4">
            <input type="date" name="date" value="{{ $date }}" class="border border-stone-300 rounded px-3 py-2 text-sm">
            <button class="px-4 py-2 rounded border border-brand text-brand text-sm font-semibold">Tampilkan</button>
        </form>
        <p class="text-sm text-stone-600 mb-2">{{ tgl($date, 'l, d F Y') }}</p>
        @forelse($slots as $s)
            <form method="POST" action="{{ route('admin.schedule.slot', $s) }}" class="flex items-center gap-3 border-t border-stone-100 py-2 text-sm">
                @csrf @method('PATCH')
                <div class="w-28 font-semibold">{{ $s->label }}</div>
                <div class="w-20 text-xs text-stone-500">Terisi {{ $s->booked_count }}/{{ $s->capacity }}</div>
                <input type="number" name="capacity" value="{{ $s->capacity }}" min="{{ max(1, $s->booked_count) }}" max="50" class="w-16 border border-stone-300 rounded px-1 py-1" title="Kapasitas">
                <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_closed" value="1" @checked($s->is_closed)> Tutup</label>
                <button class="ml-auto px-3 py-1 rounded bg-brand text-white text-xs font-semibold">Simpan</button>
            </form>
        @empty
            <p class="text-sm text-stone-500">Tidak ada slot (barbershop tutup pada hari ini atau jam operasional belum diatur).</p>
        @endforelse
    </section>
</div>
@endsection
