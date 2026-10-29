<section {{ $attributes->class(['c-events-timetable']) }}>
  @if ($periods)
    <div class="o-container">
      {{-- Figma "Events Timetable" (node 497:5333) --}}
      @foreach ($periods as $period)
        @php($short = ['Monday' => 'Mon', 'Tuesday' => 'Tues', 'Wednesday' => 'Weds', 'Thursday' => 'Thurs', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun'])
        <div class="c-events-timetable__period" data-events-timetable>
          <h{{ $headingLevel }} class="c-events-timetable__title u-heading-5">{{ $period['title'] }}</h{{ $headingLevel }}>
          @if ($period['empty'] ?? false)
            <p class="c-events-timetable__empty">{{ __('No sessions in this period.', 'sage') }}</p>
          @else
            @include('partials.events-timetable-notices', ['notices' => $period['notices'] ?? []])

            <div class="c-events-timetable__switcher">
              <button type="button" class="c-events-timetable__step c-events-timetable__step--prev" data-events-timetable-step="-1" aria-label="{{ __('Previous day', 'sage') }}">
                <x-icon name="arrow-right" />
              </button>
              <span class="c-events-timetable__current u-table-column-heading" aria-live="polite" data-events-timetable-current>{{ $period['active'] }}</span>
              <button type="button" class="c-events-timetable__step c-events-timetable__step--next" data-events-timetable-step="1" aria-label="{{ __('Next day', 'sage') }}">
                <x-icon name="arrow-right" />
              </button>
            </div>

            <div class="c-events-timetable__calendar" style="--from: {{ $period['grid']['from'] }}; --span: {{ $period['grid']['to'] - $period['grid']['from'] }}">
              <div class="c-events-timetable__hours" aria-hidden="true">
                @foreach ($period['grid']['hours'] as $hour)
                  <span class="c-events-timetable__hour" style="--at: {{ $hour['minutes'] - $period['grid']['from'] }}">{{ $hour['label'] }}</span>
                @endforeach
              </div>
              <div class="c-events-timetable__lines" aria-hidden="true"></div>

              @foreach ($period['weekdays'] as $day => $sessions)
                <section class="c-events-timetable__day" style="--col: {{ $loop->iteration + 1 }}" data-day="{{ $day }}" @if ($day === $period['active']) data-active @endif>
                  <h{{ $dayHeadingLevel }} class="c-events-timetable__day-name u-table-column-heading" @if ($day === ($period['today'] ?? null)) aria-current="date" @endif>
                    <span aria-hidden="true">{{ $short[$day] }}</span>
                    <span class="u-sr-only">{{ $day }}</span>
                  </h{{ $dayHeadingLevel }}>
                  @include('partials.events-timetable-sessions', ['sessions' => $sessions])
                </section>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif
</section>
