@props([
    'title'     => null,
    'subtitle'  => null,
    'actions'   => null,
    'footer'    => null,
    'paginator' => null,
])

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
    {{-- Card Header --}}
    @if($title || $actions)
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                @if($title)
                    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>

            @if($actions)
                <div class="flex items-center flex-wrap gap-2.5 w-full sm:w-auto justify-start sm:justify-end">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{-- Responsive Table Wrapper --}}
    <div class="overflow-x-auto w-full">
        {{ $slot }}
    </div>

    {{-- Card Footer --}}
    @if($footer || $paginator)
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            @if($paginator)
                <x-ui.table-entries :paginator="$paginator" />
                <x-ui.pagination :paginator="$paginator" />
            @else
                {{ $footer }}
            @endif
        </div>
    @endif
</div>
