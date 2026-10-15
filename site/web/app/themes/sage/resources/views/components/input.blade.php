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
    'error' => null,
])

@php($id = $id ?? $name)
@php($errorId = $error ? $id . '-error' : null)
@php($labelText = $label ?? $ariaLabel)

{{--
  $iconButtonLabel turns the trailing icon into a real, focusable
  <button type="submit"> with the yellow-cap "submit" look (e.g. a search
  field) instead of a decorative icon — pass it whenever the icon is
  something the user can actually click, not just a visual hint.

  $ariaLabel covers fields rendered without a visible $label (icon +
  placeholder only, e.g. a compact search field) — a placeholder alone
  isn't a substitute for an accessible name (WCAG 4.1.2 / 3.3.2), so one of
  $label or $ariaLabel must be supplied. wa-input/wa-textarea don't forward a
  plain `aria-label` host attribute onto their internal native control (no
  ElementInternals ARIA mixin wired up), so $ariaLabel is passed through
  wa-input's own `label` prop instead and visually hidden via
  .c-input--label-hidden when $label itself wasn't given — this still uses
  WA's real `<label for="input">`, so it's announced like any label.

  $error renders a validation message below the field via wa-input/
  wa-textarea's own `hint` prop, which already wires aria-describedby onto
  the control (WCAG 3.3.1/4.1.2). A visually-hidden role="alert" duplicate
  is also rendered alongside, since WA's hint isn't assertive on its own and
  the original behaviour announced new errors immediately.
--}}

<div {{ $attributes->class(['c-input']) }}>
    @if ($textarea)
        <wa-textarea
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            rows="{{ $rows }}"
            @if ($labelText) label="{{ $labelText }}" @endif
            @if (! $label && $ariaLabel) class="c-input--label-hidden" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($error) hint="{{ $error }}" @endif
        ></wa-textarea>
    @elseif ($icon && $iconButtonLabel)
        {{-- Icon-button variant: a real adjacent button, not a slotted overlay —
             squared off where it meets wa-input so the two read as one
             control while each keeps its own independent focus ring. --}}
        <div class="c-input__field c-input__field--icon-button">
            <wa-input
                pill
                class="c-input__field-control @if (! $label && $ariaLabel) c-input--label-hidden @endif"
                type="{{ $type }}"
                @if ($id) id="{{ $id }}" @endif
                @if ($name) name="{{ $name }}" @endif
                @if ($labelText) label="{{ $labelText }}" @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($required) required @endif
                @if ($error) hint="{{ $error }}" @endif
            ></wa-input>
            <button type="submit" class="c-input__icon c-input__icon--accent" aria-label="{{ $iconButtonLabel }}">
                <x-icon name="{{ $icon }}" />
            </button>
        </div>
    @else
        <wa-input
            pill
            type="{{ $type }}"
            @if ($id) id="{{ $id }}" @endif
            @if ($name) name="{{ $name }}" @endif
            @if ($labelText) label="{{ $labelText }}" @endif
            @if (! $label && $ariaLabel) class="c-input--label-hidden" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($error) hint="{{ $error }}" @endif
        >
            @if ($icon)
                <x-icon name="{{ $icon }}" slot="end" class="c-input__icon" />
            @endif
        </wa-input>
    @endif

    @if ($error)
        <p id="{{ $errorId }}" class="u-sr-only" role="alert">{{ $error }}</p>
    @endif
</div>
