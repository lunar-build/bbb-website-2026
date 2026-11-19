@switch($slot['type'])
  @case('image')
    <x-image class="c-two-column-content__image" :image="$slot['image']" size="large" />
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
    @include('partials.two-column-content-cta', ['link' => $slot['link']])
    @break

  @case('bullet_list')
    <x-bullet-list :heading="$slot['heading']['text']" :heading-level="$slot['heading']['level']" :heading-style="$slot['heading']['style']" :items="$slot['items']" />
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

    @include('partials.two-column-content-cta', ['link' => $slot['link'] ?? []])
@endswitch
