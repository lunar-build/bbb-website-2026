@unless ($block->preview)
  <div {{ $attributes }}>
@endunless

{{-- @if ($heading)
  <h1>{{ $heading }}</h1>
  @else
    <p>{{ $block->preview ? 'Add a heading...' : 'No heading found!' }}</p>
@endif --}}

{{-- This block uses the core/heading and core/paragraph blocks as content --}}
<div>
  <InnerBlocks template="{{ $block->template }}" />
</div>

@unless ($block->preview)
  </div>
@endunless
