@if(session('success'))
    <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
