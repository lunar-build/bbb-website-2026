<section {{ $attributes->class(['c-types-of-bikes']) }}>

<div class="o-container">
  <div class="c-types-of-bikes__cards">
    @foreach ($bikes as $bike)
      <article class="c-types-of-bikes__card">
        <div class="c-types-of-bikes__body">
          @if ($bike['illustration'])
            <img class="c-types-of-bikes__illustration" src="{{ $bike['illustration'] }}" alt="" aria-hidden="true">
          @endif

          <h3 class="c-types-of-bikes__title u-heading-5">{{ $bike['title'] }}</h3>

          @if ($bike['description'])
            <p class="c-types-of-bikes__description u-body-regular">{{ $bike['description'] }}</p>
          @endif
        </div>

        @if (! empty($bike['includes']))
          <div class="c-types-of-bikes__includes">
            <p class="c-types-of-bikes__includes-heading u-card-date">{{ __('Includes:', 'sage') }}</p>

            <ul class="c-types-of-bikes__includes-list">
              @foreach ($bike['includes'] as $include)
                <li class="c-types-of-bikes__include u-card-title-small">
                  <img class="c-types-of-bikes__include-icon" src="{{ $include['src'] }}" alt="" aria-hidden="true">
                  {{ $include['label'] }}
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      </article>
    @endforeach
  </div>
</div>

</section>
