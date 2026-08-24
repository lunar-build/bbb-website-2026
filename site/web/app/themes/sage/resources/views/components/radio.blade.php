@props([
  'label' => null,
  'name' => null,
  'value' => null,
  'id' => null,
  'checked' => false,
])

@php($id = $id ?? collect([$name, $value])->filter()->implode('-'))

<div {{ $attributes->class(['c-radio']) }}>
  <input
    type="radio"
    class="c-radio__input"
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    @if ($value !== null) value="{{ $value }}" @endif
    @if ($checked) checked @endif
  >

  @if ($label)
    <label @if ($id) for="{{ $id }}" @endif class="c-radio__label">
      {{ $label }}
    </label>
  @endif
</div>
