{{--
  No .o-container here, unlike most block views — this block only ever
  renders pre-inserted into post content (post_types restricted to
  'post', seeded via the default block template in app/filters.php),
  which content-single-post.blade.php already wraps in its own
  .o-container. A second nested one would double the side gutter.
--}}
  <section {{ $attributes->class(['c-standfirst']) }}>

<p class="c-standfirst__text u-standfirst">{{ $text }}</p>

  </section>
