<?php

namespace App\View\Composers;

use App\Support\PostCard;
use App\Support\SearchFilters;
use Roots\Acorn\View\Composer;

class Search extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'search',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        return [
            'query' => get_search_query(false),
            'filterGroups' => SearchFilters::groups(),
            'foundPosts' => (int) $GLOBALS['wp_query']->found_posts,
            'results' => array_map([$this, 'result'], $GLOBALS['wp_query']->posts),
            'paginationLinks' => pagination_links(),
        ];
    }

    /**
     * @return array{tag: string, title: string, summary: string, url: string}
     */
    protected function result(\WP_Post $post): array
    {
        return [
            'tag' => SearchFilters::tag($post),
            // get_the_title() returns entities (&#8211;) and Blade escapes again.
            'title' => html_entity_decode(get_the_title($post), ENT_QUOTES),
            // wp_trim_words() appends `&hellip;`, which Blade would escape again.
            'summary' => html_entity_decode(PostCard::summary($post), ENT_QUOTES),
            'url' => get_permalink($post),
        ];
    }
}
