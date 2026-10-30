<section {{ $attributes->class(['c-instagram-feed', 'o-full-bleed-bg']) }}>
  <div class="o-container">
    @if ($heading || $intro)
      <div class="c-instagram-feed__text">
        @if ($heading)
          <h2 class="c-instagram-feed__heading u-heading-1">{{ $heading }}</h2>
        @endif

        @if ($intro)
          <p class="c-instagram-feed__intro u-standfirst">{{ $intro }}</p>
        @endif
      </div>
    @endif

    @if ($feedShortcode)
      <div class="c-instagram-feed__feed">
        {!! do_shortcode($feedShortcode) !!}
      </div>
    @endif
  </div>
</section>
