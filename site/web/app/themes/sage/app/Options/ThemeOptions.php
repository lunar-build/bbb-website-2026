<?php

namespace App\Options;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Options as Field;

class ThemeOptions extends Field
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public $name = 'Theme Options';

    /**
     * The option page document title.
     *
     * @var string
     */
    public $title = 'Theme Options | Options';

    /**
     * The option page menu position.
     *
     * @var int
     */
    public $position = PHP_INT_MAX;

    /**
     * The option page field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('theme_options');

        $fields
            ->addRepeater('legal_links', [
                'label' => 'Legal Links',
                'instructions' => 'Links shown in the footer legal row (e.g. Privacy Policy, Terms).',
                'button_label' => 'Add Link',
            ])
                ->addText('label', [
                    'label' => 'Label',
                    'required' => true,
                ])
                ->addUrl('href', [
                    'label' => 'URL',
                    'required' => true,
                ])
            ->endRepeater();

        return $fields->build();
    }
}
