<div class="text-sm space-y-1">
    <div class="flex justify-between"><span class="text-stone-500">Tanggal</span><span class="font-semibold">{{ tgl($booking->slot->date, 'l, d F Y') }}</span></div>
    <div class="flex justify-between"><span class="text-stone-500">Slot</span><span class="font-semibold">{{ $booking->slot->label }}</span></div>
</div>
<table class="w-full text-sm mt-3">
    @foreach($booking->bookingServices as $row)
        <tr><td class="py-1">{{ $row->service->name }}</td><td class="py-1 text-right">{{ rupiah($row->price) }}</td></tr>
    @endforeach
    @foreach($booking->bookingProducts as $row)
        <tr><td class="py-1">{{ $row->product->name }} x{{ $row->qty }}</td><td class="py-1 text-right">{{ rupiah($row->price * $row->qty) }}</td></tr>
    @endforeach
    <tr class="border-t border-stone-200 font-bold"><td class="py-2">Total</td><td class="py-2 text-right text-brand">{{ rupiah($booking->total) }}</td></tr>
</table>
