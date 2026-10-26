<ul class="c-events-timetable__events" role="list">
  @forelse ($sessions as $session)
    <li class="c-events-timetable__event" style="--start: {{ $session['start'] }}; --end: {{ $session['end'] }}; --lane: {{ $session['lane'] }}; --lanes: {{ $session['lanes'] }}; --event-color: {{ $session['color'] }}">
      <a class="c-events-timetable__link" href="{{ $session['url'] }}">
        <span class="c-events-timetable__name">{{ $session['title'] }}</span>
        <span class="c-events-timetable__time">{{ $session['time'] }}</span>
      </a>
    </li>
  @empty
    <li class="u-sr-only">{{ __('No sessions on this day.', 'sage') }}</li>
  @endforelse
</ul>
