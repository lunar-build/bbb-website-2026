<section {{ $attributes->class(['c-bullet-list-block']) }}>

<div class="o-container">
  <x-bullet-list :heading="$heading['text']" :heading-level="$heading['level']" :heading-style="$heading['style']" :items="$items" :ordered="$ordered" />
</div>

</section>
