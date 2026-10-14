<section {{ $attributes->class(['c-business-card']) }}>

@if ($name)
<div class="o-container">
  <x-business-card
    :name="$name"
    :contact-info="$contactInfo"
    :services="$services"
    :description="$description"
    :link="$link"
  />
</div>
@endif

</section>
