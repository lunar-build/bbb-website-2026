<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Partial;

class ContainedToggle extends Partial
{
    /**
     * The field partial.
     *
     * Adds a "Wrap in container" toggle so a block can be dropped into
     * another block that already provides its own `.o-container` without
     * being double-contained. Use via `$fields->addPartial(ContainedToggle::class)`.
     */
    public function fields(array $args = []): Builder
    {
        $fields = Builder::make('contained_toggle');

        $fields->addTrueFalse('contained', array_merge([
            'label' => 'Wrap in container',
            'instructions' => 'Disable when placing this block inside another block that already provides its own container (e.g. a hero).',
            'default_value' => 1,
            'ui' => true,
        ], $args));

        return $fields;
    }
}
