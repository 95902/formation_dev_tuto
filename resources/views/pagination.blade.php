@if ($paginator->hasPages())
  <nav class="pagi">
    @if ($paginator->onFirstPage())
      <span class="mort">‹</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="mort">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="courant">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
    @else
      <span class="mort">›</span>
    @endif

    <span class="compte">{{ $paginator->total() }} résultats</span>
  </nav>
@endif
