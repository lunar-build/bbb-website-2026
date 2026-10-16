<section {{ $attributes->class(['c-events-timetable', 'c-events-timetable--'.$layout]) }}>
  @if ($periods)
    <div class="o-container">
      @if ($layout === 'accordion')
        {{-- Figma "Events timetable (alt)" (node 63:2406) --}}
        <wa-accordion class="c-events-timetable__periods" appearance="plain" multiple>
          @foreach ($periods as $period)
            <wa-accordion-item class="c-events-timetable__period" label="{{ $period['title'] }}" heading-level="3" @if ($loop->first) expanded @endif>
              <x-icon name="chevron" slot="icon" class="c-events-timetable__chevron" />
              @php
                // Open on today when it falls in this period, otherwise the first day with sessions.
                $open = array_search(true, array_column($period['dates'], 'today'), true);
                $open = $open !== false ? $open : (array_key_first(array_filter(array_column($period['dates'], 'sessions'))) ?? 0);
              @endphp
              @if ($period['empty'] ?? false)
                <p class="c-events-timetable__empty">{{ __('No sessions in this period.', 'sage') }}</p>
              @else
              <wa-accordion class="c-events-timetable__days" appearance="plain" multiple>
                @foreach ($period['dates'] as $date)
                  <wa-accordion-item class="c-events-timetable__day" label="{{ $date['label'] }}" heading-level="4" @if ($loop->index === $open) expanded @endif>
                    <x-icon name="chevron" slot="icon" class="c-events-timetable__chevron" />
                    @include('partials.events-timetable-sessions', ['sessions' => $date['sessions'], 'dots' => true])
                  </wa-accordion-item>
                @endforeach
              </wa-accordion>
              @endif
            </wa-accordion-item>
          @endforeach
        </wa-accordion>
      @else
        {{-- Figma "Events timetable" (node 26:1172) --}}
        @foreach ($periods as $period)
          @php
            // Open on the first day that has sessions, so a panel never starts empty.
            $active = array_key_first(array_filter($period['weekdays'])) ?? 'Monday';
          @endphp
          <div class="c-events-timetable__panel">
            <h3 class="c-events-timetable__title u-heading-5">{{ $period['title'] }}</h3>
            @if ($period['empty'] ?? false)
              <p class="c-events-timetable__empty">{{ __('No sessions in this period.', 'sage') }}</p>
            @else
            <wa-tab-group class="c-events-timetable__tabs" active="{{ strtolower($active) }}">
              @foreach ($period['weekdays'] as $day => $sessions)
                {{-- wa-tab-group only applies `active` once scrolled into view, so set it in markup too. --}}
                <wa-tab slot="nav" panel="{{ strtolower($day) }}" @if ($day === $active) active @endif>{{ $day }}</wa-tab>
              @endforeach
              @foreach ($period['weekdays'] as $day => $sessions)
                <wa-tab-panel name="{{ strtolower($day) }}" @if ($day === $active) active @endif>
                  @include('partials.events-timetable-sessions', ['sessions' => $sessions, 'dots' => false])
                </wa-tab-panel>
              @endforeach
            </wa-tab-group>
            @endif
          </div>
        @endforeach
      @endif
    </div>
  @endif
</section>
