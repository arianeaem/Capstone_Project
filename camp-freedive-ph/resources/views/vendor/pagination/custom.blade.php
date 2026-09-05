@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 py-3 bg-[#FAFAFC] border-t border-[#E5E5EA] text-xs text-[#6E6E73] rounded-b-xl">
        
        <!-- Rows Per Page Selector -->
        <div class="flex items-center gap-2">
            <span class="font-medium text-[#6E6E73]">Rows per page:</span>
            <select onchange="window.handleTablePerPageChange(this.value)"
                    class="px-2.5 py-1 rounded-lg border border-[#D1D1D6] bg-white text-xs font-bold text-[#1D1D1F] focus:outline-none focus:ring-2 focus:ring-[#780000]/20 focus:border-[#780000] cursor-pointer shadow-xs">
                @php
                    $currentPerPage = (int) request('per_page', $paginator->perPage() ?? 10);
                @endphp
                @foreach([5, 10, 15, 20, 25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ $currentPerPage === $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Pagination Result Counter -->
        <div class="font-medium text-[#6E6E73] text-center sm:text-left">
            @if ($paginator->total() > 0)
                Showing 
                <span class="font-bold text-[#1D1D1F]">{{ $paginator->firstItem() }}</span> 
                to 
                <span class="font-bold text-[#1D1D1F]">{{ $paginator->lastItem() }}</span> 
                of 
                <span class="font-bold text-[#1D1D1F]">{{ $paginator->total() }}</span> 
                results
            @else
                Showing <span class="font-bold text-[#1D1D1F]">0</span> results
            @endif
        </div>

        <!-- Pagination Navigation Controls -->
        <div class="flex items-center justify-end gap-3 flex-wrap">
            <span class="font-medium text-[#6E6E73]">
                Page <span class="font-bold text-[#1D1D1F]">{{ $paginator->currentPage() }}</span> 
                of <span class="font-bold text-[#1D1D1F]">{{ $paginator->lastPage() }}</span>
            </span>

            <nav role="navigation" aria-label="Pagination Navigation" class="inline-flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="@lang('pagination.previous')"
                          class="px-2.5 py-1 rounded-lg border border-[#E5E5EA] bg-[#F2F2F7] text-[#8E8E93] text-xs font-semibold cursor-not-allowed select-none">
                        ⟨ Prev
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"
                       class="px-2.5 py-1 rounded-lg border border-[#D1D1D6] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7] hover:border-[#8E8E93] text-xs font-bold transition-colors shadow-xs">
                        ⟨ Prev
                    </a>
                @endif

                {{-- Page Number Links (condensed on mobile) --}}
                <div class="hidden md:inline-flex items-center gap-1">
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="px-2 py-1 text-xs text-[#8E8E93] font-bold">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                          class="px-2.5 py-1 rounded-lg bg-[#780000] text-white text-xs font-black shadow-xs">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="px-2.5 py-1 rounded-lg border border-[#D1D1D6] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7] text-xs font-bold transition-colors shadow-xs">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"
                       class="px-2.5 py-1 rounded-lg border border-[#D1D1D6] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7] hover:border-[#8E8E93] text-xs font-bold transition-colors shadow-xs">
                        Next ⟩
                    </a>
                @else
                    <span aria-disabled="true" aria-label="@lang('pagination.next')"
                          class="px-2.5 py-1 rounded-lg border border-[#E5E5EA] bg-[#F2F2F7] text-[#8E8E93] text-xs font-semibold cursor-not-allowed select-none">
                        Next ⟩
                    </span>
                @endif
            </nav>
        </div>

    </div>
@endif

<script>
if (typeof window.handleTablePerPageChange !== 'function') {
    window.handleTablePerPageChange = function(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    };
}
</script>
