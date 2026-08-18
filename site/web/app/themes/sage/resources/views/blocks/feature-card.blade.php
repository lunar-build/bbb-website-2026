@unless ($block->preview)
  <section {{ $attributes->class(['c-feature-card']) }}>
@endunless

<wa-card
  class="c-feature-card__card"
  appearance="outlined"
  with-media
  @if ((! empty($link['url']) && $ctaStyle !== 'none') || ! empty($stats) || $date) with-footer @endif
>
  @if ($image)
    <img slot="media" class="c-feature-card__image" src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">
  @elseif ($block->preview)
    <div slot="media" class="c-feature-card__placeholder">
      {{ __('Add an image…', 'sage') }}
    </div>
  @endif

  {{-- Stretched-link overlay: makes the whole card clickable without nesting an
       anchor around the visible CTA below (invalid HTML). Sits behind the CTA via
       z-index/position stacking in SCSS; the CTA stays independently focusable. --}}
  @if (! empty($link['url']))
    <a
      class="c-feature-card__stretched-link"
      href="{{ $link['url'] }}"
      aria-label="{{ $link['title'] ?: get_the_title() }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    ></a>
  @endif

  <div class="c-feature-card__body">
    @if ($date)
      <p class="c-feature-card__date">{{ $date }}</p>
    @endif

    <div class="c-feature-card__content">
      <InnerBlocks template="{{ $block->template }}" />
    </div>

    @if (! empty($stats))
      <ul class="c-feature-card__stats">
        @foreach ($stats as $stat)
          <li class="c-feature-card__stat">
            <span class="c-feature-card__stat-label">{{ $stat['label'] }}</span>
            @if (! empty($stat['show_as_pill']))
              <wa-badge class="c-feature-card__stat-value" variant="{{ $stat['pill_variant'] ?: 'neutral' }}" appearance="filled" pill>
                {{ $stat['value'] }}
              </wa-badge>
            @else
              <span class="c-feature-card__stat-value">{{ $stat['value'] }}</span>
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </div>

  @if (! empty($link['url']) && $ctaStyle !== 'none')
    <div slot="footer" class="c-feature-card__footer">
      @if ($ctaStyle === 'icon')
        <span class="c-feature-card__cta-icon" aria-hidden="true">
          <wa-icon name="arrow-right"></wa-icon>
        </span>
      @else
        <span class="c-feature-card__cta" aria-hidden="true">
          {{ $link['title'] ?: __('Find out more', 'sage') }}
        </span>
      @endif
    </div>
  @endif
</wa-card>

@unless ($block->preview)
  </section>
@endunless
