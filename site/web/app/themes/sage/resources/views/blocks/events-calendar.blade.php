<section {{ $attributes->class(['c-events-calendar'])->merge(array_filter([
  'data-heading-level' => $headingLevel,
  'data-term-first-day' => $termRange['first'] ?? null,
  'data-term-last-day' => $termRange['last'] ?? null,
])) }}>

@if ($calendar)
  <div class="o-container">
    @if ($calendarMissing)
      <x-callout-text>
        {{ __('This calendar is not available at the moment.', 'sage') }}
        <a href="{{ home_url('/events/') }}">{{ __('See all events', 'sage') }}</a>
      </x-callout-text>
      @if ($block->preview || current_user_can('edit_posts'))
        {{-- Staff only: the calendar this block used to show has been deleted. --}}
        <p class="c-events-calendar__preview">
          <strong>{{ __('Editors only', 'sage') }}</strong>
          <span>{{ __('The calendar chosen for this block has been deleted, probably because its term timetable was. Click the pen icon and choose another calendar.', 'sage') }}</span>
        </p>
      @endif
    @elseif ($block->preview)
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
