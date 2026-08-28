@props([
  'label' => null,
  'name' => null,
  'id' => null,
  'placeholder' => null,
  'icon' => null,
  'iconButtonLabel' => null,
  'required' => false,
  'textarea' => false,
  'rows' => 4,
  'type' => 'text',
  'ariaLabel' => null,
])

@php($id = $id ?? $name)

{{--
  $iconButtonLabel turns the trailing icon into a real, focusable
  <button type="submit"> with the yellow-cap "submit" look (e.g. a search
  field) instead of a decorative icon — pass it whenever the icon is
  something the user can actually click, not just a visual hint.

  $ariaLabel covers fields rendered without a visible $label (icon +
  placeholder only, e.g. a compact search field) — a placeholder alone
  isn't a substitute for an accessible name (WCAG 4.1.2 / 3.3.2), so one of
  $label or $ariaLabel must be supplied.
--}}

<div {{ $attributes->class(['c-input', 'c-input--textarea' => $textarea, 'c-input--has-icon' => $icon && ! $textarea]) }}>
  @if ($label)
    <label @if ($id) for="{{ $id }}" @endif class="c-input__label">
      {{ $label }}
      @if ($required)
        <span class="c-input__required" aria-hidden="true">*</span>
      @endif
    </label>
  @endif

  <div class="c-input__field">
    @if ($textarea)
      <textarea
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
      ></textarea>
    @else
      <input
        type="{{ $type }}"
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
      >

      @if ($icon && $iconButtonLabel)
        <button type="submit" class="c-input__icon c-input__icon--accent" aria-label="{{ $iconButtonLabel }}">
          <x-icon name="{{ $icon }}" />
        </button>
      @elseif ($icon)
        <x-icon name="{{ $icon }}" class="c-input__icon" />
      @endif
    @endif
  </div>
</div>
