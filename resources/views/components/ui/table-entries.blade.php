@props([
    'paginator',
    'options' => [10, 25, 50, 100],
])

<div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500 w-full">
    {{-- Info baris --}}
    <div>
        @if($paginator && method_exists($paginator, 'total'))
            Menampilkan <span class="font-semibold text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai <span class="font-semibold text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span>
            dari <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span> entri
        @endif
    </div>

    {{-- Dropdown entries --}}
    <div class="flex items-center gap-2">
        <label for="entries-select" class="text-xs text-slate-500">Tampilkan:</label>
        <select 
            id="entries-select" 
            onchange="const url = new URL(window.location.href); url.searchParams.set('sort', this.value); window.location.href = url.toString();"
            class="py-1 px-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer"
        >
            @foreach($options as $opt)
                <option value="{{ $opt }}" {{ request('sort', 10) == $opt ? 'selected' : '' }}>
                    {{ $opt }} baris
                </option>
            @endforeach
        </select>
    </div>
</div>
