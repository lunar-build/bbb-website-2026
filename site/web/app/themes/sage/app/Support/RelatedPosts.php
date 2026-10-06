<?php

namespace App\Support;

class RelatedPosts
{
    /**
     * Fetch the latest other posts, shaped for <x-card>, for a "Also read"
     * grid that needs no editor curation.
     */
    public static function latest(int $excludeId, int $limit = 4): array
    {
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'post__not_in' => [$excludeId],
            'ignore_sticky_posts' => true,
        ]);

        return array_map(fn($post) => [
            'image' => [
                'url' => get_the_post_thumbnail_url($post, 'large') ?: '',
                'alt' => get_post_meta(get_post_thumbnail_id($post), '_wp_attachment_image_alt', true),
            ],
            'date' => get_the_date('jS F Y', $post),
            'heading' => [
                'text' => get_the_title($post),
                'level' => 'h3',
                'style' => 'match',
            ],
            // <x-card>'s stretched-link aria-label falls back to the
            // current loop post's title when this is blank — wrong here,
            // since each card links to a *different* post than the one
            // being viewed. Pass the real title through explicitly.
            'link' => [
                'url' => get_permalink($post),
                'title' => get_the_title($post),
            ],
        ], $posts);
    }
}
