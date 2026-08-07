@unless ($block->preview)
  <div {{ $attributes->class(['c-video-hero']) }}>
@endunless

<section class="c-video-hero__inner">
  <div class="c-video-hero__media">
    @if ($video['url'] ?? null)
      <video
        autoplay
        muted
        playsinline
        loop
        class="c-video-hero__video"
        aria-label="{{ $videoAlt }}"
        aria-hidden="true"
        tabindex="-1"
        @if ($poster['url'] ?? null) poster="{{ $poster['url'] }}" @endif
      >
        <source src="{{ $video['url'] }}" type="video/mp4">
        <p>{{ __("Your browser doesn't support HTML5 video.", 'sage') }}</p>
      </video>
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
        appearance="filled"
        href="{{ $ctaButton['url'] }}"
        @if (($ctaButton['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
      >
        {{ $ctaButton['title'] }}
      </wa-button>
    </div>
  </div>
</section>

@unless ($block->preview)
  </div>
@endunless
