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
            ->addTab('header', [
                'label' => 'Header',
            ])
                ->addImage('logo_charcoal', [
                    'label' => 'Logo (charcoal)',
                    'instructions' => 'Used on light backgrounds (e.g. the white top bar). Falls back to the theme default if left empty.',
                    'return_format' => 'array',
                ])
                ->addImage('logo_white', [
                    'label' => 'Logo (white)',
                    'instructions' => 'Used on dark backgrounds (e.g. the blue nav dropdown/mobile menu). Falls back to the theme default if left empty.',
                    'return_format' => 'array',
                ])
            ->addTab('social', [
                'label' => 'Social',
            ])
                ->addUrl('facebook_url', [
                    'label' => 'Facebook',
                ])
                ->addUrl('instagram_url', [
                    'label' => 'Instagram',
                ])
                ->addUrl('x_url', [
                    'label' => 'X (Twitter)',
                ])
                ->addUrl('linkedin_url', [
                    'label' => 'LinkedIn',
                ])
                ->addUrl('youtube_url', [
                    'label' => 'YouTube',
                ])
                ->addUrl('tiktok_url', [
                    'label' => 'TikTok',
                ])
            ->addTab('footer', [
                'label' => 'Footer',
            ])
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
            ->endRepeater()
            ;

        return $fields->build();
    }
}
