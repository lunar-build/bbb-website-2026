{{--
  `name`/`checked` are kept as props for back-compat but are no-ops on a bare
  <wa-radio> — name/value matching/checked state belong to the parent
  <wa-radio-group> that must wrap every group of sibling <x-radio>s (WA owns
  keyboard arrow-navigation between options at the group level, same as
  native radios). See resources/views/blocks/journey-planner-widget.blade.php
  for an example wrapping usage.
--}}
@props([
  'label' => null,
  'name' => null,
  'value' => null,
  'id' => null,
  'checked' => false,
])

<wa-radio {{ $attributes->class(['c-choice']) }} @if ($id) id="{{ $id }}" @endif value="{{ $value }}">{{ $label }}</wa-radio>
