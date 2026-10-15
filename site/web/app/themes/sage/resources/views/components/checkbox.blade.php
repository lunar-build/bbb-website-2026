@props([
  'label' => null,
  'name' => null,
  'value' => null,
  'id' => null,
  'checked' => false,
])

@php($id = $id ?? collect([$name, $value])->filter()->implode('-'))

<wa-checkbox
  {{ $attributes->class(['c-choice']) }}
  @if ($id) id="{{ $id }}" @endif
  @if ($name) name="{{ $name }}" @endif
  @if ($value !== null) value="{{ $value }}" @endif
  @checked($checked)
>{{ $label }}</wa-checkbox>
