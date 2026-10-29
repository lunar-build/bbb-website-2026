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
     * Case studies use the classic editor: a run of headings + paragraphs,
     * where each heading is a question and the content after it is the answer.
     * Content that doesn't start with a heading isn't that shape, so it renders
     * as plain content instead of being forced into rows.
     *
     * @return array{rows: array<int, array{question: string, answer: string}>, body: string}
     */
    public function details(): array
    {
        // Migrated posts still carry block comments; classic content has none.
        $content = trim(preg_replace('/<!--\s*\/?wp:.*?-->/s', '', get_post_field('post_content', get_the_ID())));

        $parts = preg_split('/(<h[1-6][^>]*>.*?<\/h[1-6]>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (! $parts || ! preg_match('/^<h[1-6]/i', $parts[0])) {
            return ['rows' => [], 'body' => apply_filters('the_content', $content)];
        }

        $rows = [];

        foreach ($parts as $part) {
            if (preg_match('/^<h[1-6]/i', $part)) {
                $rows[] = ['question' => $this->question($part), 'answer' => ''];

                continue;
            }

            $rows[array_key_last($rows)]['answer'] .= apply_filters('the_content', $part);
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
            $imageId = get_post_thumbnail_id($post);

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
