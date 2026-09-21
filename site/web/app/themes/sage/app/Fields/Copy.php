<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Partial;

class Copy extends Partial
{
    /**
     * The field partial.
     *
     * A reusable body-copy textarea paired with a visual style choice
     * (regular body copy, or a larger standfirst/intro treatment). Use via
     * `$fields->addPartial(Copy::class, ['name' => 'intro', ...])`.
     *
     * @param  array  $args  'name' (field key prefix, default 'copy'),
     *                       'label' (default 'Copy'), 'default_style'
     *                       (default 'body'), 'rows' (default 3),
     *                       'required' (default false).
     */
    public function fields(array $args = []): Builder
    {
        $name = $args['name'] ?? 'copy';
        $label = $args['label'] ?? 'Copy';
        $defaultStyle = $args['default_style'] ?? 'body';
        $rows = $args['rows'] ?? 3;
        $required = $args['required'] ?? false;

        $fields = Builder::make($name);

        $fields->addTextarea("{$name}_text", [
            'label' => $label,
            'rows' => $rows,
            'new_lines' => 'wpautop', // wraps each blank-line-separated block in <p> — matches Quote's convention, and the component using this field renders a <div> (not its own <p>) around the result
            'required' => $required,
        ]);

        $fields->addSelect("{$name}_style", [
            'label' => 'Visual style',
            'choices' => [
                'body' => 'Body copy',
                'standfirst' => 'Standfirst / intro (larger)',
            ],
            'default_value' => $defaultStyle,
            'ui' => true,
        ]);

        return $fields;
    }
}
