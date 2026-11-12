<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Partial;

class HeadingLevelSelect extends Partial
{
    /**
     * Just the heading level select, for blocks whose heading is a plain text
     * field. Add it after that field with
     * `$fields->addPartial(HeadingLevelSelect::class)`, and read it with
     * `$this->resolveField('heading_level', 'h2')`.
     *
     * @param  array  $args  'name' (default 'heading_level'), 'label', 'default_level' (default 'h2').
     */
    public function fields(array $args = []): Builder
    {
        $name = $args['name'] ?? 'heading_level';
        $label = $args['label'] ?? 'Heading level';

        $fields = Builder::make($name . '_partial');

        $fields->addSelect($name, [
            'label' => $label,
            'instructions' => 'Pick one level below the nearest heading above this block (usually H2 under the page title). Headings inside the block follow it.',
            'choices' => ['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6'],
            'default_value' => $args['default_level'] ?? 'h2',
            'ui' => true,
        ]);

        return $fields;
    }
}
