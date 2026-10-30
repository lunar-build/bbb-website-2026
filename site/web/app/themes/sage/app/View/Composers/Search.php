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
        $foundPosts = (int) $GLOBALS['wp_query']->found_posts;

        return [
            'query' => get_search_query(false),
            'filterGroups' => SearchFilters::groups(),
            'foundPosts' => $foundPosts,
            'results' => array_map([$this, 'result'], $GLOBALS['wp_query']->posts),
            'resultsLabel' => $this->resultsLabel($foundPosts),
            'paginationLinks' => pagination_links(),
        ];
    }

    /**
     * Accessible name for the results list, which takes focus after a pagination swap.
     */
    protected function resultsLabel(int $foundPosts): string
    {
        // translators: %d: total number of search results
        $label = sprintf(_n('%d search result', '%d search results', $foundPosts, 'sage'), $foundPosts);
        $totalPages = (int) $GLOBALS['wp_query']->max_num_pages;

        if ($totalPages < 2) {
            return $label;
        }

        // translators: 1: "N search results", 2: current page number, 3: total pages
        return sprintf(__('%1$s, page %2$d of %3$d', 'sage'), $label, max(1, (int) get_query_var('paged')), $totalPages);
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
