<?php

namespace App\Support;

class Breadcrumbs
{
    /**
     * Build a Home → ancestors → current-page trail for a standard WP post/page,
     * suitable for <x-breadcrumbs :items="..." />.
     */
    public static function forPost(?int $postId = null): array
    {
        $postId = $postId ?? get_the_ID();

        // Titles come back entity-encoded (&#038;) and <x-breadcrumbs> escapes them again.
        $items = [
            ['label' => __('Home', 'sage'), 'url' => home_url('/')],
        ];

        $ancestors = array_reverse(get_post_ancestors($postId));

        foreach ($ancestors as $ancestorId) {
            $items[] = [
                'label' => html_entity_decode(get_the_title($ancestorId), ENT_QUOTES),
                'url' => get_permalink($ancestorId),
            ];
        }

        $items[] = [
            'label' => html_entity_decode(get_the_title($postId), ENT_QUOTES),
        ];

        return $items;
    }
}
