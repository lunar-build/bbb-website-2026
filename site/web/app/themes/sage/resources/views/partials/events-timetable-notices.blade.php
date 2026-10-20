{{-- Closures, cancellations and extra sessions, for the weekday-tabs layout (the accordion shows them on their dates). --}}
@if ($notices)
  <div class="c-events-timetable__notices" role="note" aria-label="{{ __('Changes to this timetable', 'sage') }}">
    <h4 class="c-events-timetable__notices-title">{{ __('Changes to this timetable', 'sage') }}</h4>
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
