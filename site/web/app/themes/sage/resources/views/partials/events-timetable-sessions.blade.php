<ul class="c-events-timetable__events" role="list" aria-labelledby="{{ $headingId }}">
  @forelse ($sessions as $session)
    @php([$from, $to] = explode(' — ', $session['time']))
    <li class="c-events-timetable__event" style="--start: {{ $session['start'] }}; --end: {{ $session['end'] }}; --lane: {{ $session['lane'] }}; --lanes: {{ $session['lanes'] }}; --event-color: {{ $session['color'] }}">
      <a class="c-events-timetable__link" href="{{ $session['url'] }}">
        <span class="u-sr-only">{{ $day }}, </span>
        <span class="c-events-timetable__name">{{ $session['title'] }}</span>
        <span class="c-events-timetable__time">
          <time datetime="{{ $from }}">{{ $from }}</time> — <time datetime="{{ $to }}">{{ $to }}</time>
        </span>
        @if ($session['category'] !== '')
          <span class="c-events-timetable__category">{{ $session['category'] }}</span>
        @endif
      </a>
    </li>
  @empty
    <li class="u-sr-only">{{ __('No sessions on this day.', 'sage') }}</li>
  @endforelse
</ul>
