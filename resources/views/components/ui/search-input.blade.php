@props([
    'name'        => 'search',
    'value'       => '',
    'placeholder' => 'Cari data...',
    'formAction'  => '',
])

<form action="{{ $formAction }}" method="GET" class="relative flex items-center w-full max-w-md">
    {{-- Preserve other query params like sort if present --}}
    @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
        <x-icons.search class="w-4 h-4" />
    </div>

    <input 
        type="text" 
        name="{{ $name }}" 
        value="{{ $value ?: request($name) }}" 
        placeholder="{{ $placeholder }}"
        class="w-full pl-9 pr-8 py-2 text-sm bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-150"
    />

    @if(request($name))
        <a href="{{ url()->current() }}" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600" title="Reset pencarian">
            <x-icons.close class="w-4 h-4" />
        </a>
    @endif
</form>
