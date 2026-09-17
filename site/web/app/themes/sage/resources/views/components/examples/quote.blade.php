{{--
  Two layouts side by side. "layout" is a prop only — deliberately not an
  ACF field on the Quote block itself (resources/views/blocks/quote.blade.php
  always uses the default layout); "centered" exists for other blocks that
  embed <x-quote> directly, e.g. the carousel card (Figma node 7:2013,
  not yet built) rather than something an editor picks on the plain block.
--}}

<x-quote attribution-name="Ben" attribution-role="Cyclists in Bristol">
  <p>Find the right cycling group for you — there's plenty of choice!</p>
</x-quote>

<div style="margin-top: 2rem;">
  <x-quote layout="centered">
    <p>I ride to avoid the cost and stress of driving and parking plus getting some extra fitness in.</p>
  </x-quote>
</div>
