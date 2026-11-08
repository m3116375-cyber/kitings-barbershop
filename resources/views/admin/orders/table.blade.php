<table class="w-full text-sm">
    <thead class="bg-brand-soft text-left">
        <tr><th class="p-3">Kode</th><th class="p-3">Pelanggan</th><th class="p-3">Slot</th><th class="p-3">Status</th><th class="p-3">Pembayaran</th><th class="p-3 text-right">Total</th><th class="p-3"></th></tr>
    </thead>
    <tbody>
    @forelse($orders as $o)
        <tr class="border-t border-stone-100">
            <td class="p-3 font-semibold">{{ $o->code }}</td>
            <td class="p-3">{{ $o->user->name }}</td>
            <td class="p-3">{{ tgl($o->slot->date) }} <span class="text-stone-500">{{ jam($o->slot->start_time) }}</span></td>
            <td class="p-3"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $o->status_class }}">{{ $o->status_label }}</span></td>
            <td class="p-3">
                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $o->payment->status_class }}">{{ $o->payment->status_label }}</span>
                <span class="text-xs text-stone-500 ml-1">{{ $o->payment->method === 'midtrans' ? 'Online' : ($o->payment->method === 'cod' ? 'COD' : '-') }}</span>
            </td>
            <td class="p-3 text-right font-semibold">{{ rupiah($o->total) }}</td>
            <td class="p-3 text-right"><a href="{{ route('admin.orders.show', $o) }}" class="text-brand font-semibold">Kelola</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-6 text-center text-stone-500">Belum ada pesanan.</td></tr>
    @endforelse
    </tbody>
</table>
