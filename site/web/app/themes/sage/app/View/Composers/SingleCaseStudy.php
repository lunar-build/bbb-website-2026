<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Vite;
use Roots\Acorn\View\Composer;

class SingleCaseStudy extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.content-single-case_study',
    ];

    /**
     * Home → Case Studies → this case study, for <x-breadcrumbs>.
     */
    public function breadcrumbs(): array
    {
        return [
            ['label' => __('Home', 'sage'), 'url' => home_url('/')],
            ['label' => __('Case Studies', 'sage'), 'url' => get_post_type_archive_link('case_study')],
            ['label' => $this->name()],
        ];
    }

    /**
     * The page heading — "Case Study | John".
     */
    public function title(): string
    {
        return sprintf(__('Case Study | %s', 'sage'), $this->name());
    }

    /**
     * Split the content into the Q&A rows the design shows and anything else.
     *
     * Migrated case studies are a run of Heading (h3) + Paragraph blocks —
     * each heading is a question, the blocks after it are the answer. Content
     * that doesn't start with a heading isn't that shape, so it renders as
     * plain content instead of being forced into rows.
     *
     * @return array{rows: array<int, array{question: string, answer: string}>, body: string}
     */
    public function details(): array
    {
        $blocks = array_values(array_filter(
            parse_blocks(get_post_field('post_content', get_the_ID())),
            fn($block) => $block['blockName'] !== null,
        ));

        if (($blocks[0]['blockName'] ?? null) !== 'core/heading') {
            return ['rows' => [], 'body' => apply_filters('the_content', serialize_blocks($blocks))];
        }

        $rows = [];

        foreach ($blocks as $block) {
            if ($block['blockName'] === 'core/heading') {
                $rows[] = [
                    'question' => $this->question(render_block($block)),
                    'answer' => '',
                ];

                continue;
            }

            $rows[array_key_last($rows)]['answer'] .= render_block($block);
        }

        return ['rows' => $rows, 'body' => ''];
    }

    /**
     * Other case studies as <x-case-study-grid>-shaped cards (the grid
     * block's view is reused so the two stay identical).
     */
    public function relatedCards(): array
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');

        $posts = get_posts([
            'post_type' => 'case_study',
            'posts_per_page' => 4,
            'post__not_in' => [get_the_ID()],
        ]);

        return array_map(function ($post) use ($placeholder) {
            $imageId = (int) get_field('card_image', $post->ID) ?: get_post_thumbnail_id($post);

            return [
                'image' => [
                    'url' => ($imageId ? wp_get_attachment_image_url($imageId, 'large') : '') ?: $placeholder,
                    'alt' => $imageId ? (string) get_post_meta($imageId, '_wp_attachment_image_alt', true) : '',
                ],
                'quote' => (string) get_field('quote', $post->ID),
                'name' => '',
                'info' => '',
                'link' => [
                    'url' => get_permalink($post),
                    'title' => '',
                    'target' => '',
                ],
            ];
        }, $posts);
    }

    /**
     * Figma's "Why?" has a question mark; the migrated heading is just "Why".
     */
    protected function question(string $headingHtml): string
    {
        $question = trim(wp_strip_all_tags($headingHtml));

        return strcasecmp($question, 'why') === 0 ? $question . '?' : $question;
    }

    protected function name(): string
    {
        return html_entity_decode(get_the_title(), ENT_QUOTES);
    }
}
