<section {{ $attributes->class(['c-events-calendar'])->merge(['data-heading-level' => $headingLevel]) }}>

@if ($calendar)
  <div class="o-container">
    @if ($block->preview)
      {{-- MEC's calendars are driven by its own scripts, which don't run in the editor preview. --}}
      <p class="c-events-calendar__preview">
        <strong>{{ $title }}</strong>
        <span>{{ __('The calendar shows here on the live page.', 'sage') }}</span>
      </p>
    @else
      {!! do_shortcode('[MEC id="'.$calendar.'"]') !!}
    @endif
  </div>
@endif

</section>
