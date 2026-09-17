@unless ($block->preview)
  <section {{ $attributes->class(['c-post-hero']) }}>
@endunless

<div class="o-container">
  <div class="c-post-hero__meta">
    <p class="c-post-hero__date">{{ $date }}</p>

    <div class="c-post-hero__share">
      <p class="c-post-hero__share-label">{{ __('Share', 'sage') }}</p>

      <a
        class="c-post-hero__share-link"
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($permalink) }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="{{ __('Share on Facebook', 'sage') }}"
      >
        <x-icon name="facebook" />
      </a>

      <a
        class="c-post-hero__share-link"
        href="https://twitter.com/intent/tweet?url={{ urlencode($permalink) }}&text={{ urlencode($title) }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="{{ __('Share on X', 'sage') }}"
      >
        <x-icon name="x" />
      </a>
    </div>
  </div>

  <h1 class="c-post-hero__title">{{ $title }}</h1>

  @if (count($categories))
    <div class="c-post-hero__categories">
      @foreach ($categories as $category)
        <x-pill :label="$category['label']" :url="$category['url']" />
      @endforeach
    </div>
  @endif
</div>

@unless ($block->preview)
  </section>
@endunless
