@switch($slot['type'])
  @case('image')
    <img class="c-two-column-content__image" src="{{ $slot['image']['url'] }}" alt="{{ $slot['image']['alt'] ?? '' }}">
    @break

  @case('quote')
    {{-- TODO: reusing the Heading field as quote text and dropping citation
         entirely until the dedicated quote-component PR merges. --}}
    <span class="c-two-column-content__quote-mark" aria-hidden="true">&ldquo;</span>
    @if (! empty($slot['heading']['text']))
      <blockquote class="c-two-column-content__quote u-quote">{{ $slot['heading']['text'] }}</blockquote>
    @endif
    @break

  @case('button')
    @if (! empty($slot['link']['url']))
      <wa-button
        class="c-two-column-content__cta"
        variant="brand"
        appearance="accent"
        pill
        with-end
        href="{{ $slot['link']['url'] }}"
        @if (($slot['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
      >
        {{ $slot['link']['title'] }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    @endif
    @break

  @default
    @if (! empty($slot['heading']['text']))
      <x-heading :level="$slot['heading']['level']" :style="$slot['heading']['style']">
        {{ $slot['heading']['text'] }}
      </x-heading>
    @endif

    @if (! empty($slot['body']['text']))
      <x-copy :style="$slot['body']['style']">
        {!! $slot['body']['text'] !!}
      </x-copy>
    @endif
@endswitch
