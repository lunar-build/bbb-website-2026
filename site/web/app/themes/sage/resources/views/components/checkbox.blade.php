@props([
  'label' => null,
  'name' => null,
  'value' => null,
  'id' => null,
  'checked' => false,
])

@php($id = $id ?? collect([$name, $value])->filter()->implode('-'))

<div {{ $attributes->class(['c-choice']) }}>
  <input
    type="checkbox"
    @if ($id) id="{{ $id }}" @endif
    @if ($name) name="{{ $name }}" @endif
    @if ($value !== null) value="{{ $value }}" @endif
    @if ($checked) checked @endif
  >

  @if ($label)
    <label @if ($id) for="{{ $id }}" @endif class="c-choice__label">
      {{ $label }}
    </label>
  @endif
</div>
