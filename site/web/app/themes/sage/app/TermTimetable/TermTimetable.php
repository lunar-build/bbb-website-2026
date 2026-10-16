<?php

namespace App\TermTimetable;

/**
 * Term timetables: a term's weekly class pattern, edited on a drag-and-drop
 * screen (resources/js/admin/term-timetable) and expanded into each class's
 * MEC Custom Days on publish (MecSync).
 */
class TermTimetable
{
    public const POST_TYPE = 'bbb_term';

    public const META = '_term_schedule';

    /** Admin page slug of the React editor. */
    public const PAGE = 'bbb-term-timetable';

    public static function boot(): void
    {
        add_action('init', [static::class, 'register']);
        add_action('admin_menu', [static::class, 'adminMenu'], 20);
        add_filter('get_edit_post_link', [static::class, 'editLink'], 10, 2);
        add_action('load-post-new.php', [static::class, 'redirectNewScreen']);
        add_filter('manage_' . static::POST_TYPE . '_posts_columns', [static::class, 'columns']);
        add_action('manage_' . static::POST_TYPE . '_posts_custom_column', [static::class, 'column'], 10, 2);
        add_filter('post_row_actions', [static::class, 'rowActions'], 10, 2);
        add_action('admin_post_bbb_duplicate_term', [static::class, 'duplicate']);

        MecSync::boot();
        RestController::boot();
        AdminPage::boot();
    }

    public static function register(): void
    {
        register_post_type(static::POST_TYPE, [
            'labels' => [
                'name' => __('Term timetables', 'sage'),
                'singular_name' => __('Term timetable', 'sage'),
                'add_new_item' => __('Add term timetable', 'sage'),
                'edit_item' => __('Edit term timetable', 'sage'),
                'all_items' => __('Term timetables', 'sage'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'mec-intro',
            'show_in_rest' => true,
            // custom-fields: without it, REST neither returns nor saves the schedule meta.
            'supports' => ['title', 'custom-fields'],
            'map_meta_cap' => true,
        ]);

        register_post_meta(static::POST_TYPE, static::META, [
            'type' => 'object',
            'single' => true,
            'default' => Schedule::empty(),
            'auth_callback' => fn($allowed, $key, $postId) => current_user_can('edit_post', $postId),
            'show_in_rest' => ['schema' => Schedule::schema()],
        ]);
    }

    /** MEC's Events menu lists the post type itself; the editor page is hidden (null parent). */
    public static function adminMenu(): void
    {
        add_submenu_page(
            '',
            __('Term timetable', 'sage'),
            __('Term timetable', 'sage'),
            'edit_posts',
            static::PAGE,
            [AdminPage::class, 'render'],
        );
    }

    public static function editUrl(?int $postId = null): string
    {
        return add_query_arg(array_filter(['page' => static::PAGE, 'term' => $postId]), admin_url('admin.php'));
    }

    /** Every "edit" link for a term opens the React editor, not post.php. */
    public static function editLink($link, $postId)
    {
        return get_post_type($postId) === static::POST_TYPE ? static::editUrl((int) $postId) : $link;
    }

    /** "Add New" goes to the React editor too. */
    public static function redirectNewScreen(): void
    {
        if (($_GET['post_type'] ?? '') === static::POST_TYPE) {
            wp_safe_redirect(static::editUrl());
            exit;
        }
    }

    public static function rowActions(array $actions, \WP_Post $post): array
    {
        if ($post->post_type === static::POST_TYPE && current_user_can('edit_posts')) {
            $url = wp_nonce_url(admin_url('admin-post.php?action=bbb_duplicate_term&term=' . $post->ID), 'bbb_duplicate_term_' . $post->ID);
            $actions['duplicate'] = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('Duplicate', 'sage'));
        }

        return $actions;
    }

    /** A draft copy of a term (same week and exceptions), opened in the editor for new dates. */
    public static function duplicate(): void
    {
        $source = (int) ($_GET['term'] ?? 0);
        check_admin_referer('bbb_duplicate_term_' . $source);
        if (get_post_type($source) !== static::POST_TYPE || ! current_user_can('edit_posts')) {
            wp_die(esc_html__('You can\'t copy this term.', 'sage'));
        }

        $copy = wp_insert_post([
            'post_type' => static::POST_TYPE,
            'post_status' => 'draft',
            /* translators: %s: term name */
            'post_title' => sprintf(__('%s (copy)', 'sage'), get_the_title($source)),
        ]);
        update_post_meta($copy, static::META, Schedule::normalise(get_post_meta($source, static::META, true)));

        wp_safe_redirect(static::editUrl($copy));
        exit;
    }

    public static function columns(array $columns): array
    {
        $date = $columns['date'] ?? null;
        unset($columns['date']);

        return $columns + [
            'term_dates' => __('Runs', 'sage'),
            'term_sessions' => __('Weekly sessions', 'sage'),
        ] + ($date ? ['date' => $date] : []);
    }

    public static function column(string $column, int $postId): void
    {
        $schedule = Schedule::normalise(get_post_meta($postId, static::META, true));

        if ($column === 'term_dates' && $schedule['start'] && $schedule['end']) {
            echo esc_html(wp_date('j M Y', strtotime($schedule['start'])) . ' – ' . wp_date('j M Y', strtotime($schedule['end'])));
        }
        if ($column === 'term_sessions') {
            echo (int) count($schedule['sessions']);
        }
    }
}
