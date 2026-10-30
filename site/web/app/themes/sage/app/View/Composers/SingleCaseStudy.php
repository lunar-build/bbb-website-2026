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
     * The Q&A rows the design shows. A case study with no rows yet falls back
     * to its old content as plain text.
     *
     * @return array{rows: array<int, array{question: string, answer: string}>, body: string}
     */
    public function details(): array
    {
        $rows = [];

        foreach ((array) get_field('rows') as $row) {
            $rows[] = [
                'question' => (string) ($row['question'] ?? ''),
                'answer' => wpautop((string) ($row['answer'] ?? '')),
            ];
        }

        if ($rows) {
            return ['rows' => $rows, 'body' => ''];
        }

        $content = preg_replace('/<!--\s*\/?wp:.*?-->/s', '', get_post_field('post_content', get_the_ID()));

        return ['rows' => [], 'body' => apply_filters('the_content', trim($content))];
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

    protected function name(): string
    {
        return html_entity_decode(get_the_title(), ENT_QUOTES);
    }
}
