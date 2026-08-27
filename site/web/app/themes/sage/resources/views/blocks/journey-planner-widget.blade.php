@unless ($block->preview)
  <section {{ $attributes->class(['c-journey-planner-widget', 'c-journey-planner-widget--'.str_replace('_', '-', $variant)]) }}>
@endunless

@if ($contained)
<div class="o-container">
@endif
<div class="c-journey-planner-widget__card">
  <h2 class="c-journey-planner-widget__heading">{{ $heading }}</h2>

  @if ($variant === 'find_nearby')
    <x-input
      label="Your location"
      name="location"
      placeholder="Enter a postcode or address…"
      icon="send"
      class="c-journey-planner-widget__field"
    />

    <div class="c-journey-planner-widget__options">
      @foreach ($nearbyOptions as $i => $option)
        <x-radio name="nearby_feature" label="{{ $option['label'] }}" :checked="$i === 0" />
      @endforeach
    </div>

    {{-- TODO: wire up the "find nearby" redirect once the real endpoint/param shape is confirmed. --}}
    <wa-button variant="neutral" appearance="accent" pill with-end disabled class="c-journey-planner-widget__cta">
      {{ $ctaLabel }}
      <x-icon name="arrow-right" slot="end" />
    </wa-button>
  @else
    <form class="c-journey-planner-widget__form" data-variant="plan_route" data-redirect-url="{{ $journeyPlannerUrl }}">
      <x-input
        label="From"
        name="from"
        placeholder="Starting location"
        icon="send"
        class="c-journey-planner-widget__field"
      />

      <x-input
        label="To"
        name="to"
        placeholder="Destination"
        icon="send"
        class="c-journey-planner-widget__field"
      />

      <wa-button type="submit" variant="neutral" appearance="accent" pill with-end class="c-journey-planner-widget__cta">
        {{ $ctaLabel }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    </form>
  @endif
</div>
@if ($contained)
</div>
@endif

@unless ($block->preview)
  </section>
@endunless
