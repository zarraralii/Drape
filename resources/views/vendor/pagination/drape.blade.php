@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination__arrow is-disabled" aria-hidden="true"><x-icon name="chevron-left" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination__arrow" aria-label="Previous page"><x-icon name="chevron-left" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination__gap">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination__page is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination__page">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination__arrow" aria-label="Next page"><x-icon name="chevron-right" /></a>
        @else
            <span class="pagination__arrow is-disabled" aria-hidden="true"><x-icon name="chevron-right" /></span>
        @endif
    </nav>
@endif
