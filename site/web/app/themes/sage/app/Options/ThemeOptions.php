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
            ->addTab('general', [
                'label' => 'General',
            ])
                ->addImage('logo_charcoal', [
                    'label' => 'Logo (charcoal)',
                    'instructions' => 'Used on light backgrounds anywhere in the site (e.g. the header top bar). Falls back to the theme default if left empty.',
                    'return_format' => 'array',
                ])
                ->addImage('logo_white', [
                    'label' => 'Logo (white)',
                    'instructions' => 'Used on dark backgrounds anywhere in the site (e.g. the blue footer). Falls back to the theme default if left empty.',
                    'return_format' => 'array',
                ])
            ->addTab('header', [
                'label' => 'Header',
            ])
                ->addLink('cta', [
                    'label' => 'CTA button',
                    'instructions' => 'Shown in the header top row (e.g. "Plan a cycling route"). Leave empty to hide the button.',
                ])
                ->addLink('cta_mobile', [
                    'label' => 'CTA button (mobile)',
                    'instructions' => 'Shorter link/text for mobile, where the desktop CTA text may not fit. Leave empty to reuse the desktop CTA button above on mobile too.',
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
                ->addText('official_site', [
                    'label' => 'Official cycling website',
                    'instructions' => 'Shown in the footer bottom bar, e.g. "Official cycling website for Bristol, Bath & NE Somerset, North Somerset and South Gloucestershire". Leave empty to fall back to the site tagline.',
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
            ->addTab('not_found', [
                'label' => '404 page',
            ])
            ->addRepeater('not_found_links', [
                'label' => 'Quick links',
                'instructions' => 'Buttons shown under the search field on the 404 page (e.g. Home, Journey planner, What\'s on, News). Leave empty to show those four.',
                'button_label' => 'Add Link',
                'max' => 6,
            ])
                ->addLink('link', [
                    'label' => 'Link',
                    'required' => true,
                ])
            ->endRepeater()
            ->addTab('integrations', [
                'label' => 'Integrations',
            ])
                ->addText('google_maps_api_key', [
                    'label' => 'Google Maps API key',
                    'instructions' => 'Used by the Journey Planner Widget block (Places API) and the Map Listing template (Maps JavaScript API) — enable both APIs on the key and restrict it to this site\'s domain.',
                ])
                ->addText('google_maps_map_id', [
                    'label' => 'Google Maps map ID',
                    'instructions' => 'Optional cloud-styled map ID for the Map Listing template\'s map. Leave empty for Google\'s default styling.',
                ])
            ->addTab('archive_pages', [
                'label' => 'Archive pages',
            ])
                ->addMessage('archive_pages_info', 'News uses the "Posts page" set in Settings → Reading. Pick a page for any other archive below — its Image Hero and intro content render above the archive\'s filters and results.', [
                    'label' => 'Archive pages',
                ]);

        foreach (get_post_types(['has_archive' => true, '_builtin' => false], 'objects') as $postType) {
            $fields->addPostObject("archive_page_{$postType->name}", [
                'label' => $postType->labels->name,
                'post_type' => ['page'],
                'return_format' => 'id',
                'allow_null' => true,
                'ui' => true,
            ]);
        }

        return $fields->build();
    }
}
