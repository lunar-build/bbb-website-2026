<section {{ $attributes->class(['c-bullet-list-block']) }}>

<div class="o-container">
  <x-bullet-list :heading="$heading" :heading-level="$headingLevel" :items="$items" :ordered="$ordered" />
</div>

</section>
