<div class="tablenav-pages">
    <span class="displaying-num">{{ $posts->total() }} {{ $posts->total() === 1 ? __('item') : __('items') }}</span>
    @if ($posts->lastPage() > 1)
        @if ($posts->onFirstPage())
            <span class="paging-btn disabled" aria-hidden="true">«</span>
            <span class="paging-btn disabled" aria-hidden="true">‹</span>
        @else
            <a class="paging-btn" href="{{ $posts->url(1) }}">«</a>
            <a class="paging-btn" href="{{ $posts->previousPageUrl() }}">‹</a>
        @endif
        <span class="paging-input">{{ $posts->currentPage() }} {{ __('of') }} {{ $posts->lastPage() }}</span>
        @if ($posts->hasMorePages())
            <a class="paging-btn" href="{{ $posts->nextPageUrl() }}">›</a>
            <a class="paging-btn" href="{{ $posts->url($posts->lastPage()) }}">»</a>
        @else
            <span class="paging-btn disabled" aria-hidden="true">›</span>
            <span class="paging-btn disabled" aria-hidden="true">»</span>
        @endif
    @endif
</div>
