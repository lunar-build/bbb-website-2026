{{-- Rows for both Events Timetable layouts; $dots adds the accordion's colour dot. --}}
@if ($sessions)
  <ul class="c-events-timetable__sessions" role="list">
    @foreach ($sessions as $session)
      <li class="c-events-timetable__session">
        @if ($dots)
          <span class="c-events-timetable__dot" style="--dot: {{ $session['color'] }}" aria-hidden="true"></span>
        @endif
        <span class="c-events-timetable__time">{{ $session['time'] }}</span>
        <a class="c-events-timetable__link" href="{{ $session['url'] }}">
          {{ $session['title'] }}
          <x-icon name="arrow-right" />
        </a>
      </li>
    @endforeach
  </ul>
@else
  <p class="c-events-timetable__empty">{{ __('No sessions on this day.', 'sage') }}</p>
@endif
