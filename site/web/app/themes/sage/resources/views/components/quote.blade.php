{{--
  Reusable pull-quote fragment. Used by resources/views/blocks/quote.blade.php
  (default slot = the Quote block's rendered InnerBlocks content) and free to
  hardcode directly into any other block's Blade view later (default slot =
  literal text/HTML in that case instead).

  Attribution is optional and hides entirely when $attributionName is empty;
  $attributionRole only renders when both are present.
--}}

@props([
    'attributionName' => null,
    'attributionRole' => null,
])

<div {{ $attributes->class(['c-quote']) }}>
    <span class="c-quote__mark" aria-hidden="true">&ldquo;</span>

    <blockquote class="c-quote__body">
        {{ $slot }}
    </blockquote>

    @if ($attributionName)
        <cite class="c-quote__attribution">
            <span class="c-quote__attribution-name">{{ $attributionName }}</span>
            @if ($attributionRole)
                <span class="c-quote__attribution-role">{{ $attributionRole }}</span>
            @endif
        </cite>
    @endif
</div>
