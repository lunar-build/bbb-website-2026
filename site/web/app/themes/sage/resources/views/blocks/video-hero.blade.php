@unless ($block->preview)
  <section {{ $attributes->class(['c-video-hero']) }}>
@endunless

<div class="c-video-hero__media">
  @if ($video['url'] ?? null)
    <lunar-video
      variant="background"
      class="c-video-hero__video"
      src="{{ $video['url'] }}"
      @if ($poster['url'] ?? null) poster="{{ $poster['url'] }}" @endif
      label="{{ $videoAlt }}"
    ></lunar-video>
  @elseif ($poster['url'] ?? null)
    <img class="c-video-hero__video" src="{{ $poster['url'] }}" alt="{{ $videoAlt }}">
  @elseif ($block->preview)
    <div class="c-video-hero__placeholder">
      {{ __('Add a video and/or poster image…', 'sage') }}
    </div>
  @endif
</div>

<div class="c-video-hero__lower">
  <div class="o-container">
    <div class="c-video-hero__content">
      <h1 class="c-video-hero__heading">{{ $heading }}</h1>
      <p class="c-video-hero__intro">{{ $intro }}</p>
    </div>
  </div>

  {{-- Widget slot — overlaps the media/lower boundary per Figma. In its
       own .o-container (rather than sharing the content's) so it's
       gutter/max-width constrained independently and can be right-aligned
       within it via justify-self. Empty by default in the editor until a
       Journey Planner Widget (or another block added to $allowedBlocks) is
       inserted; not shown at all on the pattern-library page since
       nested-block fixture content isn't wired up there (see
       App\View\Composers\PatternLibrary). --}}
  <div class="o-container">
    <div class="c-video-hero__widget">
      <InnerBlocks allowedBlocks="{{ json_encode($block->allowedBlocks) }}" template="{{ $block->template }}" />
    </div>
  </div>
</div>

@unless ($block->preview)
  </section>
@endunless
