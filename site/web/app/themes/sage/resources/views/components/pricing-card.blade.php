@props(['title', 'price', 'description' => null, 'headingLevel' => 'h4'])

<div {{ $attributes->class(['c-pricing-card__card']) }}>
  <div class="c-pricing-card__header">
    <x-heading :level="$headingLevel" style="h4" class="c-pricing-card__title">{{ $title }}</x-heading>
    <span class="c-pricing-card__price">{{ $price }}</span>
  </div>

  @if ($description)
    <hr class="c-pricing-card__rule">

    <p class="c-pricing-card__description">{{ $description }}</p>
  @endif
</div>
