@unless ($block->preview)
  <section {{ $attributes->class(['c-form']) }}>
@endunless

  <div class="c-form__inner">
    <div class="c-form__content">
      <x-heading :level="$heading['level']" :style="$heading['style']">
        {{ $heading['text'] }}
      </x-heading>
      @if (! empty($subheading['text']))
        <x-heading :level="$subheading['level']" :style="$subheading['style']">
          {{ $subheading['text'] }}
        </x-heading>
      @endif
      @if (! empty($intro['text']))
        <x-copy :style="$intro['style']">
          {{ $intro['text'] }}
        </x-copy>
      @endif
    </div>

    <div class="c-form__form">
      @if ($block->preview)
        <p class="c-form__placeholder">
          {{ sprintf(__('Gravity Form #%d will render here.', 'sage'), $formId) }}
        </p>
      @else
        @php gravity_form($formId, false, false, false, null, false, 0, true); @endphp
      @endif
    </div>
  </div>

@unless ($block->preview)
  </section>
@endunless
