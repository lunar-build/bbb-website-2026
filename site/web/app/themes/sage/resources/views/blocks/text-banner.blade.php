<x-block-root :heading="$heading" {{ $attributes->class(['c-text-banner', 'o-full-bleed-bg']) }}>

<div class="o-container">
  <div class="c-text-banner__inner">
    @if ($heading['text'])
      <x-heading :level="$heading['level']" :style="$heading['style']" class="c-text-banner__heading">
        {!! $heading['text'] !!}
      </x-heading>
    @endif

    @if ($body)
      <div class="c-text-banner__body u-body-large u-rich-text">{!! $body !!}</div>
    @endif
  </div>
</div>

</x-block-root>
