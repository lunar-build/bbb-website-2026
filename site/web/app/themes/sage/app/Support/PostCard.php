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
            'date' => $post->post_type === 'mec-events' ? self::eventDate($post) : get_the_date('jS F Y', $post),
            'categories' => self::categories($post),
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
     * Category tags for <x-pill>, minus WP's default "Uncategorized" term
     * that every uncategorised post still carries.
     */
    public static function categories(\WP_Post $post): array
    {
        $categories = array_filter(
            get_the_category($post->ID),
            fn($category) => $category->term_id !== (int) get_option('default_category'),
        );

        return array_values(array_map(fn($category) => [
            'label' => wp_specialchars_decode($category->name),
            'url' => get_category_link($category),
        ], $categories));
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

    /**
     * Events show their next (or last) session, not their publish date.
     */
    protected static function eventDate(\WP_Post $post): string
    {
        $shown = EventSessions::forDisplay($post->ID);

        return $shown ? gmdate('jS F Y', $shown['session']->tstart) : '';
    }
}
