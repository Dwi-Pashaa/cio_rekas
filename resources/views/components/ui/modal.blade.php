@props([
    'id',
    'title',
    'size'       => 'md',
    'formId'     => null,
    'formAction' => null,
    'formMethod' => 'POST',
    'submitText' => 'Simpan',
    'cancelText' => 'Batal',
    'footer'     => null,
])

@php
    $sizeClasses = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
    ];
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $sizeClasses[$size] ?? '' }}">
        <div class="modal-content border-0 shadow-xl rounded-2xl overflow-hidden">
            {{-- Modal Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h5 class="text-base font-semibold text-slate-800 m-0" id="{{ $id }}Label">
                    {{ $title }}
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none" data-bs-dismiss="modal" aria-label="Close">
                    <x-icons.close class="w-4 h-4" />
                </button>
            </div>

            {{-- Optional Form wrapper or Body --}}
            @if($formId)
                <form id="{{ $formId }}" action="{{ $formAction }}" method="{{ $formMethod }}">
                    @csrf
            @endif

            <div class="p-5 modal-body">
                {{ $slot }}
            </div>

            {{-- Modal Footer --}}
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                @if($footer)
                    {{ $footer }}
                @else
                    <button type="button" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer" data-bs-dismiss="modal">
                        {{ $cancelText }}
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                        {{ $submitText }}
                    </button>
                @endif
            </div>

            @if($formId)
                </form>
            @endif
        </div>
    </div>
</div>
