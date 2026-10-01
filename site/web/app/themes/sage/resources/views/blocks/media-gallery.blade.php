<section {{ $attributes->class(['c-media-gallery']) }}>

<div class="o-container">
  <div class="c-media-gallery__wrapper" data-media-gallery>

    @if (count($pages) > 1 && ! $isEditorPreview)
      <wa-carousel class="c-media-gallery__carousel" navigation pagination loop mouse-dragging slides-per-page="1">
        @foreach ($pages as $page)
          <wa-carousel-item class="c-media-gallery__item">
            <x-media-gallery-grid :items="$page" :is-editor-preview="$isEditorPreview" />
          </wa-carousel-item>
        @endforeach

        <span slot="previous-icon" class="c-media-gallery__nav-icon c-media-gallery__nav-icon--previous">
          <x-icon name="arrow-right" />
        </span>
        <span slot="next-icon" class="c-media-gallery__nav-icon">
          <x-icon name="arrow-right" />
        </span>
      </wa-carousel>
    @elseif ($isEditorPreview)
      {{-- wa-carousel's own internal click/drag handling (see its
           handleClick/handleMouseDragStart) intercepts clicks before they
           bubble to Gutenberg's "select this block" handler, making a
           multi-page gallery unselectable by clicking it — not just the
           tile buttons (see <x-media-gallery-grid>'s isEditorPreview
           branch), the carousel wrapper itself. Stack every page's grid
           plainly instead; paging chrome isn't needed to edit fields. --}}
      @foreach ($pages as $page)
        <x-media-gallery-grid :items="$page" :is-editor-preview="$isEditorPreview" />
      @endforeach
    @else
      <x-media-gallery-grid :items="$pages[0] ?? []" :is-editor-preview="$isEditorPreview" />
    @endif

    <wa-dialog
      class="c-media-gallery__dialog"
      data-media-gallery-dialog
      label="{{ __('Media preview', 'sage') }}"
      light-dismiss
    >
      <div class="c-media-gallery__dialog-body" data-media-gallery-dialog-body></div>
    </wa-dialog>

  </div>
</div>

</section>
