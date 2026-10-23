<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Partial;

class Heading extends Partial
{
    /**
     * The field partial.
     *
     * A reusable heading block: free text + a semantic level (h1-h6, drives
     * the actual HTML tag via <x-heading>) decoupled from a visual style
     * (which u-heading-* size it's rendered at) — so an editor can pick an
     * H2 for document-outline correctness while still sizing it like an H4.
     * Use via `$fields->addPartial(Heading::class, ['name' => 'heading', ...])`.
     *
     * @param  array  $args  'name' (field key prefix, default 'heading'),
     *                       'label' (default 'Heading'), 'default_level'
     *                       (default 'h2'), 'default_style' (default 'match'),
     *                       'required' (default false), 'instructions' (text
     *                       field help), 'rich' (a basic rich-text field
     *                       instead of plain text, default false),
     *                       'allow_none' (adds a "Not a heading" level that
     *                       outputs a styled <div>, default false).
     */
    public function fields(array $args = []): Builder
    {
        $name = $args['name'] ?? 'heading';
        $label = $args['label'] ?? 'Heading';
        $defaultLevel = $args['default_level'] ?? 'h2';
        $defaultStyle = $args['default_style'] ?? 'match';
        $required = $args['required'] ?? false;
        $levels = [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
        ];

        if ($args['allow_none'] ?? false) {
            $levels['none'] = 'Not a heading (just styled text)';
        }

        $fields = Builder::make($name);

        $text = [
            'label' => $label,
            'instructions' => $args['instructions'] ?? '',
            'required' => $required,
        ];

        if ($args['rich'] ?? false) {
            $fields->addWysiwyg("{$name}_text", $text + [
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ]);
        } else {
            $fields->addText("{$name}_text", $text);
        }

        $fields->addSelect("{$name}_level", [
            'label' => 'Heading level',
            'instructions' => 'Semantic HTML heading level — choose based on the page\'s document outline/SEO, not how big it should look.',
            'choices' => $levels,
            'default_value' => $defaultLevel,
            'ui' => true,
        ]);

        $fields->addSelect("{$name}_style", [
            'label' => 'Visual style',
            'instructions' => 'Override how the heading looks without changing its semantic level — e.g. an H2 sized like an H4.',
            'choices' => [
                'match' => 'Match level',
                'h1' => 'Heading 1 size',
                'h2' => 'Heading 2 size',
                'h3' => 'Heading 3 size',
                'h4' => 'Heading 4 size',
                'h5' => 'Heading 5 / 6 size',
            ],
            'default_value' => $defaultStyle,
            'ui' => true,
        ]);

        return $fields;
    }
}
