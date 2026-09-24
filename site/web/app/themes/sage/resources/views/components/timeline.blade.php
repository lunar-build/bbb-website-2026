@props(['steps' => []])

<ol {{ $attributes->class(['c-timeline']) }}>
  @foreach ($steps as $index => $step)
    <li class="c-timeline__step">
      <span class="c-timeline__marker u-heading-4">{{ $step['label'] ?: $index + 1 }}</span>

      <div class="c-timeline__content">
        <h3 class="c-timeline__heading u-heading-5">{{ $step['heading'] }}</h3>

        @if (! empty($step['body']))
          <p class="c-timeline__body u-body-regular">{!! $step['body'] !!}</p>
        @endif
      </div>
    </li>
  @endforeach
</ol>
