<?php

namespace App\Support;

class RelatedPosts
{
    /**
     * Fetch the latest other posts, shaped for <x-card>, for a "Also read"
     * grid that needs no editor curation.
     */
    public static function latest(int $excludeId, int $limit = 3, string $postType = 'post'): array
    {
        $posts = get_posts([
            'post_type' => $postType,
            'posts_per_page' => $limit,
            'post__not_in' => [$excludeId],
            'ignore_sticky_posts' => true,
        ]);

        // Three across the narrow post column — the full h3 scale wraps to 4-5 lines.
        return array_map(fn($post) => PostCard::fromPost($post, 'match'), $posts);
    }
}
