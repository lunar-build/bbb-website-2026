<?php

namespace App\Support;

/**
 * Search results' "Filter by content type" checkboxes (?filter_type[]=page),
 * applied to the main query in app/filters.php. Same form/fragment-swap
 * wiring as the archive filters (resources/js/archive-filters.js).
 */
class SearchFilters
{
    public const PARAM = 'filter_type';

    /**
     * Searchable post types that have published content, as slug => plural label.
     *
     * @return array<string, string>
     */
    public static function postTypes(): array
    {
        $types = get_post_types(['public' => true, 'exclude_from_search' => false], 'objects');
        unset($types['attachment']);

        $types = array_filter($types, fn($type) => (int) (wp_count_posts($type->name)->publish ?? 0) > 0);

        return array_map(fn($type) => $type->labels->name, $types);
    }

    /**
     * @return string[]
     */
    public static function active(): array
    {
        $requested = array_map('sanitize_key', (array) ($_GET[self::PARAM] ?? []));

        return array_values(array_intersect($requested, array_keys(self::postTypes())));
    }

    /**
     * Ticked types, or every searchable type when none are ticked.
     *
     * @return string[]
     */
    public static function queryPostTypes(): array
    {
        return self::active() ?: array_keys(self::postTypes());
    }

    /**
     * One group shaped for <x-filter-checkboxes>.
     */
    public static function groups(): array
    {
        $active = self::active();
        $items = [];

        foreach (self::postTypes() as $slug => $label) {
            $items[] = [
                'label' => $label,
                'name' => self::PARAM . '[]',
                'value' => $slug,
                'id' => self::PARAM . "-{$slug}",
                'checked' => in_array($slug, $active, true),
            ];
        }

        return count($items) > 1
            ? [['heading' => __('Filter by content type', 'sage'), 'items' => $items]]
            : [];
    }

    /**
     * Tag shown on a result, e.g. "Page", "News", "Event (ended)".
     */
    public static function tag(\WP_Post $post): string
    {
        $label = get_post_type_object($post->post_type)->labels->singular_name ?? '';

        if ($post->post_type === 'mec-events' && EventSessions::hasEnded($post->ID)) {
            /* translators: %s: post type label, e.g. "Event" */
            return sprintf(__('%s (ended)', 'sage'), $label);
        }

        return $label;
    }
}
