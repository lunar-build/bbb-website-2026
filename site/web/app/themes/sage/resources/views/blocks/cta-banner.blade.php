<section {{ $attributes->class(['c-cta-banner', 'c-cta-banner--'.$layout]) }}>

@if ($layout === 'centred')
  @if ($imageLeft)
    <img class="c-cta-banner__image c-cta-banner__image--left" src="{{ $imageLeft }}" alt="">
  @endif

  @if ($imageRight)
    <img class="c-cta-banner__image c-cta-banner__image--right" src="{{ $imageRight }}" alt="">
  @endif
@endif

<div class="o-container">
  <div class="c-cta-banner__grid">
    <div class="c-cta-banner__content">
      <div class="c-cta-banner__text">
        @if ($heading)
          <h2 class="c-cta-banner__heading u-heading-1">{{ $heading }}</h2>
        @endif

        <p class="c-cta-banner__body u-standfirst">{{ $body }}</p>
      </div>

      @if (! empty($link['url']))
        <wa-button
          class="c-cta-banner__cta"
          variant="brand"
          appearance="accent"
          pill
          with-end
          href="{{ $link['url'] }}"
          @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
        >
          {{ $link['title'] ?: __('Find out more', 'sage') }}
          <x-icon name="arrow-right" slot="end" />
        </wa-button>
      @endif
    </div>

    @if (in_array($layout, ['left', 'row'], true) && $image)
      <img class="c-cta-banner__image--column" src="{{ $image }}" alt="">
    @endif
  </div>
</div>

</section>
