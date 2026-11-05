@if ($paginator->hasPages())
<nav class="g-pager" aria-label="Halaman">
    @if ($paginator->onFirstPage())<span class="mati">‹ Sebelumnya</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Sebelumnya</a>@endif
    <span aria-current="page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya ›</a>@else<span class="mati">Berikutnya ›</span>@endif
</nav>
@endif
