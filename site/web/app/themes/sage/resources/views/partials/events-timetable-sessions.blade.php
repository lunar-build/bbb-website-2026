{{-- Rows for both Events Timetable layouts; $dots adds the accordion's colour dot. --}}
@if ($sessions)
  <ul class="c-events-timetable__sessions" role="list">
    @foreach ($sessions as $session)
      <li @class(['c-events-timetable__session', 'c-events-timetable__session--cancelled' => $session['cancelled'] ?? false])>
        @if ($dots)
          <span class="c-events-timetable__dot" style="--dot: {{ $session['color'] }}" aria-hidden="true"></span>
        @endif
        <span class="c-events-timetable__time">{{ $session['time'] }}</span>
        <a class="c-events-timetable__link" href="{{ $session['url'] }}">
          {{ $session['title'] }}
          @foreach ($session['labels'] ?? [] as $label)
            <span class="c-label c-label--small" style="--label-bg: {{ $label['bg'] }}; --label-fg: {{ $label['fg'] }}">{{ $label['name'] }}</span>
          @endforeach
          <x-icon name="arrow-right" />
        </a>
        @if ($session['extra'] ?? false)
          <span class="c-events-timetable__cancelled">
            <span class="c-label c-label--small" style="--label-bg: #dff3e4; --label-fg: #1c5b2e">{{ __('Extra session', 'sage') }}</span>
          </span>
        @endif
        @if ($session['cancelled'] ?? false)
          <span class="c-events-timetable__cancelled">
            <span class="c-label c-label--small" style="--label-bg: #fbe4e4; --label-fg: #8a1c1c">{{ __('Cancelled', 'sage') }}</span>
            @if ($session['reason'])
              <span class="c-events-timetable__reason">{{ $session['reason'] }}</span>
            @endif
          </span>
        @endif
      </li>
    @endforeach
  </ul>
@else
  <p class="c-events-timetable__empty">{{ ($closed ?? null) ? sprintf(__('Closed: %s', 'sage'), $closed) : __('No sessions on this day.', 'sage') }}</p>
@endif
