@unless ($block->preview)
  <section {{ $attributes->class(['c-two-column-content']) }}>
@endunless

<div class="o-container">
  <div class="c-two-column-content__inner c-two-column-content--{{ $layout }} @if ($flip) c-two-column-content--flip @endif">
    <div class="c-two-column-content__media">
      @if ($layout === 'quote_text')
        {{-- TODO: reusing the Heading field as quote text and dropping
             citation entirely until the dedicated quote-component PR
             merges — swap this block out for real quote fields then. --}}
        <span class="c-two-column-content__quote-mark" aria-hidden="true">&ldquo;</span>
        @if (! empty($heading['text']))
          <blockquote class="c-two-column-content__quote u-quote">{{ $heading['text'] }}</blockquote>
        @endif
      @else
        <img class="c-two-column-content__image" src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">
      @endif
    </div>

    <div class="c-two-column-content__content">
      @if ($layout === 'quote_text')
        @if (! empty($body['text']))
          <x-copy :style="$body['style']">
            {!! $body['text'] !!}
          </x-copy>
        @endif
      @else
        @if (! empty($heading['text']))
          <x-heading :level="$heading['level']" :style="$heading['style']">
            {{ $heading['text'] }}
          </x-heading>
        @endif

        @if (! empty($body['text']))
          <x-copy :style="$body['style']">
            {!! $body['text'] !!}
          </x-copy>
        @endif

        @if (! empty($link['url']))
          <wa-button
            class="c-two-column-content__cta"
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
        @endif
      @endif
    </div>
  </div>
</div>

@unless ($block->preview)
  </section>
@endunless
