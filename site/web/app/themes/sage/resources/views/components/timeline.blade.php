@props(['steps' => [], 'markerSize' => 'number'])

<ol {{ $attributes->class(['c-timeline', 'c-timeline--dates' => $markerSize === 'date']) }}>
  @foreach ($steps as $index => $step)
    @php
      // Custom labels only apply to wide markers; number timelines always count steps.
      $label = $markerSize === 'date' && ! empty($step['label']) ? $step['label'] : $index + 1;
    @endphp

    <li @class(['c-timeline__step', 'c-timeline__step--no-heading' => empty($step['heading'])])>
      <span class="c-timeline__marker-col">
        <span class="c-timeline__marker u-heading-4">{{ $label }}</span>
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
