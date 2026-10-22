@php($itemLabel = $itemLabel ?? 'lapangan')
@php($paginationLabel = $paginationLabel ?? 'Halaman daftar lapangan')
@if($paginator->hasPages())
<nav class="catalog-pagination" aria-label="{{ $paginationLabel }}">
    <p>Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} {{ $itemLabel }}</p>
    <div>
        @if($paginator->onFirstPage())
            <span aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif
        <span aria-current="page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span aria-disabled="true">Berikutnya</span>
        @endif
    </div>
</nav>
@endif
