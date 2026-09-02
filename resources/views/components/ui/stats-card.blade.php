@props([
    'title',
    'value',
    'subtitle' => null,
    'color'    => 'blue',
])

@php
    $colorMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100'],
        'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100'],
    ];

    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 flex items-start justify-between">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
            {{ $title }}
        </p>
        <h4 class="text-2xl font-bold text-slate-800 tracking-tight">
            {{ $value }}
        </h4>
        @if($subtitle)
            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if(isset($icon))
        <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center shrink-0 border {{ $c['border'] }}">
            {{ $icon }}
        </div>
    @endif
</div>
