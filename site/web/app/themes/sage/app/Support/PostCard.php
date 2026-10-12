<?php

namespace App\Support;

class PostCard
{
    /**
     * Shape a post for <x-card card-style="news">.
     */
    public static function fromPost(\WP_Post $post, string $headingStyle = 'h5'): array
    {
        // get_the_title() returns entities (&#8211;) and <x-card> escapes again.
        $title = html_entity_decode(get_the_title($post), ENT_QUOTES);

        return [
            'image' => [
                'url' => get_the_post_thumbnail_url($post, 'large') ?: '',
                'alt' => get_post_meta(get_post_thumbnail_id($post), '_wp_attachment_image_alt', true),
            ],
            'date' => get_the_date('jS F Y', $post),
            'heading' => [
                'text' => $title,
                'level' => 'h3',
                'style' => $headingStyle,
            ],
            'body' => [
                'text' => esc_html(self::summary($post)),
                'style' => 'body',
            ],
            // <x-card>'s aria-label falls back to the current loop post's title when blank — wrong here, since each card links elsewhere.
            'link' => [
                'url' => get_permalink($post),
                'title' => $title,
                'label' => __('Read more', 'sage'),
            ],
        ];
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
