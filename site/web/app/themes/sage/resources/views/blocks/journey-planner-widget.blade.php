  <section {{ $attributes->class(['c-journey-planner-widget', 'c-journey-planner-widget--'.str_replace('_', '-', $variant)]) }}>

@if ($contained)
<div class="o-container">
@endif
<div class="c-journey-planner-widget__card">
  @php $headingId = 'c-journey-planner-widget__heading-'.uniqid(); @endphp
  <h2 id="{{ $headingId }}" class="c-journey-planner-widget__heading">{{ $heading }}</h2>

  @if ($variant === 'find_nearby')
    <form class="c-journey-planner-widget__form" aria-labelledby="{{ $headingId }}" data-variant="find_nearby" data-redirect-url="{{ $journeyPlannerUrl }}">
      <x-input
        label="Your location"
        name="location"
        placeholder="Enter a postcode or address…"
        icon="send"
        icon-button-label="Use my current location"
        icon-button-type="button"
        icon-inline
        class="c-journey-planner-widget__field"
      />
      <p id="location-error" class="u-sr-only" role="alert"></p>

      <wa-radio-group
        class="c-journey-planner-widget__options"
        label="What are you looking for?"
        name="nearby_feature"
        value="{{ array_key_first($nearbyOptions) }}"
      >
        @foreach ($nearbyOptions as $value => $label)
          <x-radio :label="$label" :value="$value" />
        @endforeach
      </wa-radio-group>

      <wa-button type="submit" variant="neutral" appearance="accent" pill with-end class="c-journey-planner-widget__cta">
        {{ $ctaLabel }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    </form>
  @else
    <form class="c-journey-planner-widget__form" aria-labelledby="{{ $headingId }}" data-variant="plan_route" data-redirect-url="{{ $journeyPlannerUrl }}">
      <x-input
        label="From"
        name="from"
        placeholder="Starting location"
        icon="send"
        class="c-journey-planner-widget__field"
      />
      <p id="from-error" class="u-sr-only" role="alert"></p>

      <x-input
        label="To"
        name="to"
        placeholder="Destination"
        icon="send"
        class="c-journey-planner-widget__field"
      />
      <p id="to-error" class="u-sr-only" role="alert"></p>

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

  </section>
