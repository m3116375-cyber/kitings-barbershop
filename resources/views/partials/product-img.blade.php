@if($product->image_url)
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="{{ $size ?? 'w-full h-32' }} object-cover rounded bg-stone-200">
@else
    <div class="{{ $size ?? 'w-full h-32' }} rounded bg-stone-200 flex items-center justify-center text-stone-400 text-xs shrink-0">foto</div>
@endif
