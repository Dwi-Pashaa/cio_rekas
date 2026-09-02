@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed">
                <x-icons.chevron-left class="w-3.5 h-3.5 mr-1" />
                Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors">
                <x-icons.chevron-left class="w-3.5 h-3.5 mr-1" />
                Prev
            </a>
        @endif

        {{-- Page Numbers (Compact on Mobile, Full on Desktop) --}}
        <div class="hidden sm:flex items-center gap-1">
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span aria-current="page" class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-white bg-blue-600 rounded-lg shadow-sm">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition-colors">
                Next
                <x-icons.chevron-right class="w-3.5 h-3.5 ml-1" />
            </a>
        @else
            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed">
                Next
                <x-icons.chevron-right class="w-3.5 h-3.5 ml-1" />
            </span>
        @endif
    </nav>
@endif
