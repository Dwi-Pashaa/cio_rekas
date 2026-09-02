@props([
    'label'       => null,
    'name',
    'id'          => null,
    'type'        => 'text',
    'value'       => '',
    'placeholder' => '',
    'required'    => false,
    'disabled'    => false,
    'errorName'   => null,
])

@php
    $inputId = $id ?: $name;
    $errClass = $errorName ?: $name;
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $inputId }}" 
            value="{{ $value }}" 
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'w-full px-3.5 py-2 text-sm bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-150 disabled:bg-slate-100 disabled:text-slate-400']) }}
        />
    </div>

    <span class="text-xs text-rose-500 mt-1 block invalid-feedback error_{{ $errClass }}"></span>
</div>
