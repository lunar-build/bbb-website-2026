<?php

namespace App\Support;

class ArchiveFilters
{
    /**
     * Taxonomies never offered as archive filters.
     */
    protected const EXCLUDED_TAXONOMIES = ['post_tag', 'post_format'];

    /**
     * Prefixed so it never collides with WP's own query vars (e.g. `category_name`).
     */
    public static function param(string $taxonomy): string
    {
        return "filter_{$taxonomy}";
    }

    /**
     * The post type the current archive request lists.
     */
    public static function postType(): string
    {
        if (is_post_type_archive()) {
            $postType = get_query_var('post_type');

            return is_array($postType) ? reset($postType) : $postType;
        }

        if (is_tax()) {
            $taxonomy = get_taxonomy(get_queried_object()->taxonomy ?? '');

            return $taxonomy->object_type[0] ?? 'post';
        }

        return 'post';
    }

    /**
     * @return array<string, \WP_Taxonomy>
     */
    public static function taxonomies(string $postType): array
    {
        return array_filter(
            get_object_taxonomies($postType, 'objects'),
            fn($taxonomy) => $taxonomy->public && $taxonomy->show_ui && ! in_array($taxonomy->name, self::EXCLUDED_TAXONOMIES, true),
        );
    }

    /**
     * Selected term slugs per taxonomy, from the request plus the queried
     * term on a term archive.
     *
     * @return array<string, string[]>
     */
    public static function active(string $postType): array
    {
        $queried = get_queried_object();
        $active = [];

        foreach (self::taxonomies($postType) as $taxonomy) {
            $slugs = array_map('sanitize_title', (array) ($_GET[self::param($taxonomy->name)] ?? []));

            if ($queried instanceof \WP_Term && $queried->taxonomy === $taxonomy->name) {
                $slugs[] = $queried->slug;
            }

            if ($slugs = array_values(array_unique(array_filter($slugs)))) {
                $active[$taxonomy->name] = $slugs;
            }
        }

        return $active;
    }

    /**
     * OR within a checkbox group, AND across groups.
     */
    public static function taxQuery(string $postType): array
    {
        $active = self::active($postType);

        if (! $active) {
            return [];
        }

        $clauses = [];

        foreach ($active as $taxonomy => $slugs) {
            // Same keys as groups(): a term's parent decides which checkbox group it's in.
            $byGroup = collect($slugs)->groupBy(fn($slug) => get_term_by('slug', $slug, $taxonomy)->parent ?? 0);

            foreach ($byGroup as $groupSlugs) {
                $clauses[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $groupSlugs->all(),
                ];
            }
        }

        return ['relation' => 'AND', ...$clauses];
    }

    /**
     * Groups shaped for <x-filter-checkboxes>. A hierarchical taxonomy gets
     * one group per parent term that has children (e.g. Region → Bristol,
     * Bath...), with its childless top-level terms under the taxonomy label.
     * Filtering ORs terms within a group and ANDs across groups (taxQuery()).
     */
    public static function groups(string $postType): array
    {
        $active = self::active($postType);
        $groups = [];

        foreach (self::taxonomies($postType) as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy->name,
                'hide_empty' => true,
                'exclude' => $taxonomy->name === 'category' ? [(int) get_option('default_category')] : [],
            ]);

            if (is_wp_error($terms) || ! $terms) {
                continue;
            }

            $param = self::param($taxonomy->name);
            $checked = $active[$taxonomy->name] ?? [];

            $item = fn(\WP_Term $term) => [
                'label' => html_entity_decode($term->name, ENT_QUOTES),
                'name' => "{$param}[]",
                'value' => $term->slug,
                'id' => "{$param}-{$term->slug}",
                'checked' => in_array($term->slug, $checked, true),
            ];

            $children = collect($terms)->where('parent', '!=', 0)->groupBy('parent');
            $topLevel = collect($terms)->where('parent', 0);

            $standalone = $topLevel->reject(fn($term) => $children->has($term->term_id));

            if ($standalone->isNotEmpty()) {
                $groups[] = [
                    'heading' => $taxonomy->labels->name,
                    'items' => $standalone->map($item)->values()->all(),
                ];
            }

            foreach ($topLevel->filter(fn($term) => $children->has($term->term_id)) as $parent) {
                $groups[] = [
                    'heading' => html_entity_decode($parent->name, ENT_QUOTES),
                    'items' => $children->get($parent->term_id)->map($item)->values()->all(),
                ];
            }
        }

        return $groups;
    }

    /**
     * Unfiltered first-page URL for the archive — the filter form's action,
     * so unticking a term archive's own term doesn't stay on that term's URL.
     */
    public static function baseUrl(string $postType): string
    {
        if ($postType === 'post') {
            $page = (int) get_option('page_for_posts');

            return $page ? get_permalink($page) : home_url('/');
        }

        return get_post_type_archive_link($postType) ?: home_url('/');
    }
}
