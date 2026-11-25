@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman" class="d-flex gap-2 justify-content-center">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline-light disabled">Previous</span>
        @else
            <a class="btn btn-sm btn-outline-light" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif
        @if ($paginator->hasMorePages())
            <a class="btn btn-sm btn-success" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="btn btn-sm btn-outline-light disabled">Next</span>
        @endif
    </nav>
@endif
