{{-- Date-specific changes; the calendar only shows the weekly pattern. --}}
@if ($notices)
  <div class="c-events-timetable__notices" role="note" aria-label="{{ __('Changes to this timetable', 'sage') }}">
    <h{{ $headingLevel }} class="c-events-timetable__notices-title">{{ __('Changes to this timetable', 'sage') }}</h{{ $headingLevel }}>
    <ul role="list">
      @foreach ($notices as $notice)
        <li class="c-events-timetable__notice c-events-timetable__notice--{{ $notice['type'] }}">
          <span class="c-events-timetable__notice-when">{{ $notice['when'] }}</span>
          <span class="c-events-timetable__notice-text">{{ $notice['text'] }}</span>
        </li>
      @endforeach
    </ul>
  </div>
@endif
