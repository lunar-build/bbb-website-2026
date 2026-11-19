{{-- Links sit straight in the nav (no list), so screen readers announce them as pagination links. --}}
@if (count($paginationLinks))
  <nav class="c-archive__pagination" aria-label="{{ __('Pagination', 'sage') }}">
    @foreach ($paginationLinks as $link)
      {!! $link !!}
    @endforeach
  </nav>
@endif
