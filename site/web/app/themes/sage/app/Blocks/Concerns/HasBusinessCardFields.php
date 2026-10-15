<?php

namespace App\Blocks\Concerns;

/**
 * Shared field definitions and rendering helpers for the "Business Card"
 * shape (name, contact info, services, description, link) — used by both
 * the standalone App\Blocks\BusinessCard block and Card Grid's "Business
 * card" card style, so the two stay in lockstep rather than drifting.
 */
trait HasBusinessCardFields
{
    /**
     * Icon choices shared by the Contact info repeater — a fixed,
     * developer-controlled set rendered via <wa-icon>, matching how
     * FeatureCard.php's `stats` repeater constrains its `pill_variant`
     * select rather than allowing arbitrary input.
     *
     * @var array
     */
    protected $contactIconChoices = [
        'location-dot' => 'Location',
        'phone' => 'Phone',
        'envelope' => 'Email',
        'globe' => 'Website',
    ];

    /**
     * Icon choices shared by the Services repeater.
     *
     * @var array
     */
    protected $serviceIconChoices = [
        'bicycle' => 'Bicycle',
        'wrench' => 'Servicing/repair',
        'recycle' => 'Secondhand/refurbished',
        'hand-holding-heart' => 'Donate',
        'shop' => 'Shop',
        'gift' => 'Hire/loan',
    ];

    /**
     * Add the Business Card sub-fields (name, contact info, services,
     * description, link) onto the given field/repeater builder — used both
     * at a block's top level and nested inside a "cards" repeater.
     *
     * @param  mixed  $fields
     * @param  string  $prefix
     * @return mixed
     */
    protected function addBusinessCardFields($fields, string $prefix = '')
    {
        return $fields
            ->addText("{$prefix}name", [
                'label' => 'Name',
                'required' => 1,
            ])
            ->addRepeater("{$prefix}contact_info", [
                'label' => 'Contact info',
                'instructions' => 'Address, phone, etc. Leave empty to omit.',
                'button_label' => 'Add contact info',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => $this->contactIconChoices,
                    'default_value' => 'location-dot',
                    'ui' => true,
                ])
                ->addText('text', [
                    'label' => 'Text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addRepeater("{$prefix}services", [
                'label' => 'Services',
                'instructions' => 'Short tag-style service labels. Leave empty to omit.',
                'button_label' => 'Add service',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => $this->serviceIconChoices,
                    'default_value' => 'bicycle',
                    'ui' => true,
                ])
                ->addText('text', [
                    'label' => 'Text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addTextarea("{$prefix}description", [
                'label' => 'Description',
                'rows' => 3,
            ])
            ->addLink("{$prefix}link", [
                'label' => 'Link',
                'instructions' => 'Link text + URL for the CTA.',
                'required' => true,
            ]);
    }

    /**
     * Build the tap/click-through URL for a contact info row based on its
     * icon type.
     *
     * @param  string  $icon
     * @param  string  $text
     * @return string|null
     */
    protected function businessCardContactHref($icon, $text)
    {
        return match ($icon) {
            'phone' => 'tel:' . preg_replace('/[^0-9+]/', '', $text),
            'envelope' => 'mailto:' . trim($text),
            'location-dot' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($text),
            'globe' => preg_match('#^https?://#i', $text) ? $text : 'https://' . $text,
            default => null,
        };
    }

    /**
     * Augment a Business Card's contact info rows with a tap/click-through
     * `href` per row.
     *
     * @param  array  $rows
     * @return array
     */
    protected function withBusinessCardContactHrefs(array $rows)
    {
        return array_map(fn($row) => $row + ['href' => $this->businessCardContactHref($row['icon'], $row['text'])], $rows);
    }
}
