<section {{ $attributes->class(['c-text-banner', 'o-full-bleed-bg']) }}>

<div class="o-container">
  <div class="c-text-banner__inner c-text-banner__inner--{{ $alignment }}">
    @if ($heading)
      <div class="c-text-banner__heading">{!! $heading !!}</div>
    @endif

    @if ($body)
      <div class="c-text-banner__body u-body-large">{!! $body !!}</div>
    @endif
  </div>
</div>

</section>
