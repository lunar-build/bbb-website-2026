<?php

namespace App\Support;

class RelatedPosts
{
    /**
     * Fetch the latest other posts, shaped for <x-card>, for a "Also read"
     * grid that needs no editor curation.
     */
    public static function latest(int $excludeId, int $limit = 3): array
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
                // Three across the narrow post column — the full h3 scale wraps to 4-5 lines.
                'style' => 'h5',
            ],
            'body' => [
                'text' => esc_html(self::summary($post)),
                'style' => 'body',
            ],
            // <x-card>'s aria-label falls back to the current loop post's title when blank — wrong here, since each card links elsewhere.
            'link' => [
                'url' => get_permalink($post),
                'title' => get_the_title($post),
                'label' => __('Read more', 'sage'),
            ],
        ], $posts);
    }

    /**
     * Hand-written excerpt, else the post's Standfirst (its intended summary,
     * which WP's auto-excerpt skips since it's an ACF block), else the
     * auto-excerpt from the opening paragraphs.
     */
    private static function summary(\WP_Post $post): string
    {
        $text = $post->post_excerpt;

        if (! $text) {
            foreach (parse_blocks($post->post_content) as $block) {
                if ($block['blockName'] === 'acf/standfirst') {
                    $text = $block['attrs']['data']['text'] ?? '';
                    break;
                }
            }
        }

        $text = $text ?: get_the_excerpt($post);

        return wp_trim_words(html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES), 20);
    }
}
