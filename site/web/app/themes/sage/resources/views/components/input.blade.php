@props([
  'label' => null,
  'name' => null,
  'id' => null,
  'placeholder' => null,
  'icon' => null,
  'required' => false,
  'textarea' => false,
  'rows' => 4,
])

@php($id = $id ?? $name)

<div {{ $attributes->class(['c-input', 'c-input--textarea' => $textarea]) }}>
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
      ></textarea>
    @else
      <input
        type="text"
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
      >

      @if ($icon)
        <x-icon name="{{ $icon }}" class="c-input__icon" />
      @endif
    @endif
  </div>
</div>
