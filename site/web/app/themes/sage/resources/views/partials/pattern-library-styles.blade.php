{{--
  Hand-maintained style primitives for the pattern library's dev-only
  "Components" section — buttons, the type scale, and the callout style
  aren't separate files like resources/views/components/*.blade.php, so
  there's nothing to auto-discover. Add a couple of lines here only when a
  genuinely new style primitive (not a component, not a block) is built.
--}}

<section id="style-buttons" class="c-pattern-library__entry">
  <div class="c-pattern-library__meta">
    <h2>{{ __('Buttons', 'sage') }}</h2>
  </div>

  <div class="c-pattern-library__preview">
    <wa-button variant="brand" appearance="accent" pill with-end>
      {{ __('Brand button', 'sage') }}
      <x-icon name="arrow-right" slot="end" />
    </wa-button>

    <wa-button variant="neutral" appearance="accent" pill with-end>
      {{ __('Neutral button', 'sage') }}
      <x-icon name="arrow-right" slot="end" />
    </wa-button>
  </div>
</section>

<section id="style-type-scale" class="c-pattern-library__entry">
  <div class="c-pattern-library__meta">
    <h2>{{ __('Type scale', 'sage') }}</h2>
  </div>

  <div class="c-pattern-library__preview">
    <p class="u-heading-1">{{ __('Heading 1', 'sage') }}</p>
    <p class="u-heading-2">{{ __('Heading 2', 'sage') }}</p>
    <p class="u-heading-3">{{ __('Heading 3', 'sage') }}</p>
    <p class="u-heading-4">{{ __('Heading 4', 'sage') }}</p>
    <p class="u-heading-5">{{ __('Heading 5', 'sage') }}</p>
    <p class="u-standfirst">{{ __('Standfirst text', 'sage') }}</p>
    <p class="u-input-label">{{ __('Input label text', 'sage') }}</p>
    <p>{{ __('Body text', 'sage') }}</p>
  </div>
</section>

<section id="style-callout" class="c-pattern-library__entry">
  <div class="c-pattern-library__meta">
    <h2>{{ __('Callout', 'sage') }}</h2>
  </div>

  <div class="c-pattern-library__preview">
    <p class="u-callout">{{ __('Callout text', 'sage') }}</p>
  </div>
</section>
