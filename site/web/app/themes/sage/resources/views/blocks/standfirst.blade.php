{{-- No .o-container — this only ever renders inside content-single-post.blade.php, which already wraps it in one. --}}
  <div {{ $attributes->class(['c-standfirst']) }}>

<p class="c-standfirst__text u-standfirst">{{ $text }}</p>

  </div>
