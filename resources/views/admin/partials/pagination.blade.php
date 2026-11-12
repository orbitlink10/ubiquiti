@if($paginator->hasPages())
    <nav class="pager admin-pagination" aria-label="{{ $label }} pagination">
        @if($paginator->onFirstPage())
            <span class="pager-link disabled" aria-disabled="true">Previous</span>
        @else
            <a class="pager-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif
        <span aria-current="page">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} ({{ $paginator->total() }} total)</span>
        @if($paginator->hasMorePages())
            <a class="pager-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="pager-link disabled" aria-disabled="true">Next</span>
        @endif
    </nav>
@endif
