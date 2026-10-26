@props(['steps' => [], 'markerSize' => 'number'])

<ol {{ $attributes->class(['c-timeline', 'c-timeline--dates' => $markerSize === 'date']) }}>
  @foreach ($steps as $index => $step)
    @php
      $label = $step['label'] ?: $index + 1;
      $date = null;

      // DD/MM/YY labels stack as day / month / year; anything else shows as typed.
      if ($markerSize === 'date' && preg_match('/^(\d{2})\/(\d{2})\/(\d{2})$/', $step['label'] ?? '', $matches)) {
        $date = DateTimeImmutable::createFromFormat('!d/m/y', $step['label']) ?: null;
      }
    @endphp

    <li @class(['c-timeline__step', 'c-timeline__step--no-heading' => empty($step['heading'])])>
      <span class="c-timeline__marker-col">
        @if ($date)
          <time class="c-timeline__marker c-timeline__marker--date" datetime="{{ $date->format('Y-m-d') }}">
            <span class="c-timeline__date-day">{{ $date->format('j') }}</span>
            <span class="c-timeline__date-month">{{ date_i18n('M', $date->getTimestamp()) }}</span>
            <span class="c-timeline__date-year">{{ $date->format('Y') }}</span>
          </time>
        @else
          <span class="c-timeline__marker u-heading-4">{{ $label }}</span>
        @endif
      </span>

      <div class="c-timeline__content">
        @if (! empty($step['heading']))
          <h3 class="c-timeline__heading u-heading-5">{{ $step['heading'] }}</h3>
        @endif

        @if (! empty($step['body']))
          <p class="c-timeline__body u-body-regular">{!! $step['body'] !!}</p>
        @endif
      </div>
    </li>
  @endforeach
</ol>
