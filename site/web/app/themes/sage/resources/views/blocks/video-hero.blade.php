@unless ($block->preview)
  <section {{ $attributes->class(['c-video-hero']) }}>
@endunless

<div class="o-container">
<div class="c-video-hero__inner">
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

  <div class="c-video-hero__content">
    <h1 class="c-video-hero__heading">{{ $heading }}</h1>
    <p class="c-video-hero__intro">{{ $intro }}</p>

    <div class="c-video-hero__cta">
      <p class="c-video-hero__cta-text">
        <strong class="c-video-hero__cta-label">{{ $ctaLabel }}</strong><br>
        {{ $ctaSubtext }}
      </p>

      <wa-button
        variant="brand"
        appearance="accent"
        pill
        with-end
        href="{{ $ctaButton['url'] }}"
        @if (($ctaButton['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
      >
        {{ $ctaButton['title'] }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    </div>
  </div>
</div>
</div>

@unless ($block->preview)
  </section>
@endunless
