<?php

namespace App\View\Composers;

use App\Support\RelatedPosts;
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
     * Same shape as SinglePost::hero(), with case study tags in place of categories.
     */
    public function hero(): array
    {
        $tags = get_the_terms(get_the_ID(), 'case_study_tag');

        return [
            'date' => get_the_date('jS F Y'),
            'dateIso' => get_the_date('c'),
            'title' => html_entity_decode(get_the_title(), ENT_QUOTES),
            'permalink' => get_permalink(),
            'categories' => is_array($tags) ? array_map(fn($tag) => [
                'label' => wp_specialchars_decode($tag->name),
                'url' => add_query_arg('filter_case_study_tag[]', $tag->slug, get_post_type_archive_link('case_study')),
            ], $tags) : [],
        ];
    }

    public function quote(): string
    {
        return (string) get_field('quote');
    }

    public function relatedPosts(): array
    {
        return RelatedPosts::latest(get_the_ID(), 3, 'case_study');
    }
}
