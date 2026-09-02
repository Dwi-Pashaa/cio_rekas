@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
    'href'    => null,
])

@php
    $baseClass = "inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none rounded-xl cursor-pointer";
    
    $variants = [
        'primary'   => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow active:bg-blue-800 border border-transparent',
        'secondary' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm hover:border-slate-300',
        'outline'   => 'bg-transparent border border-blue-600 text-blue-600 hover:bg-blue-50',
        'danger'    => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm hover:shadow active:bg-rose-800 border border-transparent',
        'ghost'     => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2 min-h-[40px]',
        'lg' => 'px-5 py-2.5 text-base gap-2.5 min-h-[48px]',
    ];

    $classes = $baseClass . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
