<section {{ $attributes->class(['c-icon-text-grid', 'o-full-bleed-bg', 'c-icon-text-grid--center' => $textAlign === 'center']) }}>
  <div class="o-container">
    <ul class="c-icon-text-grid__list" role="list">
      @foreach ($items as $item)
        <li class="c-icon-text-grid__item">
          <x-icon name="{{ $item['icon'] }}" class="c-icon-text-grid__icon" />

          @if (! empty($item['body']))
            <p class="c-icon-text-grid__body">{!! nl2br(e($item['body'])) !!}</p>
          @endif
        </li>
      @endforeach
    </ul>
  </div>
</section>
