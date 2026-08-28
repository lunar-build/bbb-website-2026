@unless ($block->preview)
  <section {{ $attributes->class(['c-cta-banner', 'c-cta-banner--'.$layout]) }}>
@endunless

@if ($layout === 'centred' && $imageLeft)
  <img class="c-cta-banner__image c-cta-banner__image--left" src="{{ $imageLeft }}" alt="">
@endif

@if ($layout === 'centred' && $imageRight)
  <img class="c-cta-banner__image c-cta-banner__image--right" src="{{ $imageRight }}" alt="">
@elseif ($layout === 'left' && $image)
  <img class="c-cta-banner__image c-cta-banner__image--right" src="{{ $image }}" alt="">
@endif

<div class="o-container">
  <div class="c-cta-banner__content">
    @if ($heading)
      <h2 class="c-cta-banner__heading u-heading-1">{{ $heading }}</h2>
    @endif

    <p class="c-cta-banner__body u-standfirst">{{ $body }}</p>

    <wa-button
      class="c-cta-banner__cta"
      variant="brand"
      appearance="accent"
      pill
      with-end
      href="{{ $link['url'] }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $link['title'] }}
      <x-icon name="arrow-right" slot="end" />
    </wa-button>
  </div>
</div>

@unless ($block->preview)
  </section>
@endunless
