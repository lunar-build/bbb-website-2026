@props(['steps' => [], 'markerSize' => 'number'])

<ol {{ $attributes->class(['c-timeline', 'c-timeline--dates' => $markerSize === 'date']) }}>
  @foreach ($steps as $index => $step)
    @php
      $label = $step['label'] ?: $index + 1;
      $datetime = null;

      if ($markerSize === 'date' && preg_match('/^(\d{2})\/(\d{2})\/(\d{2})$/', $step['label'] ?? '', $matches)) {
        $datetime = "20{$matches[3]}-{$matches[2]}-{$matches[1]}";
      }
    @endphp

    <li class="c-timeline__step">
      <span class="c-timeline__marker-col">
        @if ($datetime)
          <time class="c-timeline__marker u-heading-4" datetime="{{ $datetime }}">{{ $label }}</time>
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
