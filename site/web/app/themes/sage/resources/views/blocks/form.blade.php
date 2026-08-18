@unless ($block->preview)
  <section {{ $attributes->class(['c-form']) }}>
@endunless

  <div class="c-form__inner">
    <div class="c-form__content">
      <InnerBlocks template="{{ $block->template }}" />
    </div>

    <div class="c-form__form">
      @if ($block->preview)
        <p class="c-form__placeholder">
          {{ sprintf(__('Gravity Form #%d will render here.', 'sage'), $formId) }}
        </p>
      @elseif (function_exists('gravity_form'))
        @php gravity_form($formId, false, false, false, null, false, 0, true); @endphp
      @endif
    </div>
  </div>

@unless ($block->preview)
  </section>
@endunless
