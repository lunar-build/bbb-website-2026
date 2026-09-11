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

        $items = [
            ['label' => __('Home', 'sage'), 'url' => home_url('/')],
        ];

        $ancestors = array_reverse(get_post_ancestors($postId));

        foreach ($ancestors as $ancestorId) {
            $items[] = [
                'label' => get_the_title($ancestorId),
                'url' => get_permalink($ancestorId),
            ];
        }

        $items[] = [
            'label' => get_the_title($postId),
        ];

        return $items;
    }
}
