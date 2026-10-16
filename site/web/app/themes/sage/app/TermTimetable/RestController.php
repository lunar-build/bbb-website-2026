<?php

namespace App\TermTimetable;

use WP_Error;
use WP_REST_Request;

/**
 * Routes the term editor needs beyond core's wp/v2/bbb_term CRUD:
 * the class palette, and a no-save preview of what a schedule generates.
 */
class RestController
{
    public const NS = 'bbb/v1';

    public static function boot(): void
    {
        add_action('rest_api_init', [static::class, 'routes']);
        add_filter('rest_pre_insert_' . TermTimetable::POST_TYPE, [static::class, 'blockInvalidPublish'], 10, 2);
    }

    public static function routes(): void
    {
        $can = fn() => current_user_can('edit_posts');

        register_rest_route(static::NS, '/term-categories', [
            'methods' => 'GET',
            'permission_callback' => $can,
            'callback' => fn() => array_map(
                fn($term) => ['id' => $term->term_id, 'name' => html_entity_decode($term->name, ENT_QUOTES)],
                get_terms(['taxonomy' => 'mec_category', 'hide_empty' => false]) ?: [],
            ),
        ]);

        register_rest_route(static::NS, '/term-classes', [
            'methods' => 'GET',
            'permission_callback' => $can,
            'args' => ['category' => ['type' => 'integer', 'default' => 0]],
            'callback' => fn(WP_REST_Request $request) => static::classes((int) $request['category']),
        ]);

        register_rest_route(static::NS, '/term-preview', [
            'methods' => 'POST',
            'permission_callback' => $can,
            'args' => [
                'schedule' => ['type' => 'object', 'required' => true],
                'term' => ['type' => 'integer', 'default' => 0],
            ],
            'callback' => fn(WP_REST_Request $request) => static::preview((array) $request['schedule'], (int) $request['term']),
        ]);
    }

    /** Published classes in a category (all categories if 0), with their MEC colour. */
    public static function classes(int $category): array
    {
        $posts = get_posts([
            'post_type' => 'mec-events',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'tax_query' => $category ? [['taxonomy' => 'mec_category', 'terms' => $category]] : [],
        ]);

        return array_map(fn($post) => [
            'id' => $post->ID,
            'title' => html_entity_decode(get_the_title($post), ENT_QUOTES),
            'color' => \App\Support\EventSessions::color($post->ID),
        ], $posts);
    }

    /**
     * What saving this schedule would do: sessions per class and total,
     * plus validation errors/warnings and checks that need WordPress
     * (missing classes, other terms overlapping these dates).
     */
    public static function preview(array $schedule, int $termId = 0): array
    {
        $schedule = Schedule::normalise($schedule);
        $check = Schedule::validate($schedule);
        $counts = array_map('count', Schedule::expand($schedule));

        foreach (array_keys($counts) as $event) {
            if (get_post_type($event) !== 'mec-events' || get_post_status($event) !== 'publish') {
                $check['warnings'][] = ['message' => sprintf(__('Class #%d isn\'t published any more, so its sessions will be skipped.', 'sage'), $event), 'ref' => "event:$event"];
            }
        }

        if ($schedule['start'] && $schedule['end']) {
            foreach (get_posts(['post_type' => TermTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1, 'exclude' => array_filter([$termId])]) as $other) {
                $o = Schedule::normalise(get_post_meta($other->ID, TermTimetable::META, true));
                if ((int) $o['category'] === (int) $schedule['category'] && $o['start'] <= $schedule['end'] && $o['end'] >= $schedule['start']) {
                    $check['warnings'][] = ['message' => sprintf(__('These dates overlap “%s”. Both will run.', 'sage'), get_the_title($other)), 'ref' => 'dates'];
                }
            }
        }

        return $check + ['counts' => (object) $counts, 'total' => array_sum($counts)];
    }

    /** A term can be saved as a draft whatever state it's in, but not published with errors. */
    public static function blockInvalidPublish($prepared, WP_REST_Request $request)
    {
        $status = $prepared->post_status ?? get_post_status($prepared->ID ?? 0);
        if ($status !== 'publish') {
            return $prepared;
        }
        $schedule = $request['meta'][TermTimetable::META] ?? get_post_meta($prepared->ID ?? 0, TermTimetable::META, true);
        $errors = Schedule::validate(Schedule::normalise($schedule))['errors'];

        return $errors
            ? new WP_Error('term_invalid', $errors[0]['message'], ['status' => 400, 'errors' => $errors])
            : $prepared;
    }
}
