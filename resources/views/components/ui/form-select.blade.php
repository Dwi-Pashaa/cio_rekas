@props([
    'label'       => null,
    'name',
    'id'          => null,
    'required'    => false,
    'disabled'    => false,
    'placeholder' => '-- Pilih Opsi --',
    'errorName'   => null,
])

@php
    $selectId = $id ?: $name;
    $errClass = $errorName ?: $name;
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select 
            name="{{ $name }}" 
            id="{{ $selectId }}" 
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'w-full px-3.5 py-2 text-sm bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-150 appearance-none cursor-pointer pr-10']) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
            <x-icons.chevron-down class="w-4 h-4" />
        </div>
    </div>

    <span class="text-xs text-rose-500 mt-1 block invalid-feedback error_{{ $errClass }}"></span>
</div>
