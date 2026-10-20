<?php

namespace App\EventTimetable;

use Illuminate\Support\Facades\Vite;

/** The React timetable editor's admin page (resources/js/admin/event-timetable). */
class AdminPage
{
    public const SCREEN = 'admin_page_' . EventTimetable::PAGE;

    /** WordPress packages the app imports; listed explicitly so it also works under the Vite dev server. */
    public const DEPENDENCIES = ['react', 'react-dom', 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n', 'wp-dom-ready', 'wp-a11y'];

    public static function boot(): void
    {
        add_action('admin_enqueue_scripts', [static::class, 'enqueue']);
        add_action('admin_head', [static::class, 'printEntry']);
    }

    protected static function isScreen(): bool
    {
        return get_current_screen()?->id === static::SCREEN;
    }

    public static function enqueue(): void
    {
        if (! static::isScreen()) {
            return;
        }
        foreach (static::DEPENDENCIES as $handle) {
            wp_enqueue_script($handle);
        }
        wp_enqueue_style('wp-components');
    }

    public static function printEntry(): void
    {
        if (! static::isScreen()) {
            return;
        }
        $timetableId = (int) ($_GET['timetable'] ?? 0);
        $timetable = $timetableId ? get_post($timetableId) : null;
        if ($timetable && $timetable->post_type !== EventTimetable::POST_TYPE) {
            $timetableId = 0;
        }

        // Standard post locking: claim the timetable, or say who already has it open.
        $lockedBy = null;
        if ($timetableId) {
            $holder = wp_check_post_lock($timetableId);
            $lockedBy = $holder ? get_userdata($holder)?->display_name : null;
            if (! $holder) {
                wp_set_post_lock($timetableId);
            }
        }

        $default = get_term_by('slug', 'bristol-cycling-centre', 'mec_category');

        printf('<script>window.bbbEventTimetable = %s;</script>', wp_json_encode([
            'timetableId' => $timetableId,
            'listUrl' => admin_url('edit.php?post_type=' . EventTimetable::POST_TYPE),
            'editUrl' => EventTimetable::editUrl(),
            'defaultCategory' => $default ? $default->term_id : 0,
            'lockedBy' => $lockedBy,
            'canPublish' => current_user_can('publish_posts'),
        ]));
        echo Vite::withEntryPoints(['resources/js/admin/event-timetable/index.jsx'])->toHtml();
    }

    public static function render(): void
    {
        // wp-header-end: WordPress moves admin notices here, above the editor rather than inside it.
        printf(
            '<div class="wrap bbb-tt-wrap"><h1 class="wp-heading-inline">%s</h1><hr class="wp-header-end"><div id="bbb-event-timetable"></div></div>',
            esc_html(isset($_GET['timetable']) ? __('Edit event timetable', 'sage') : __('New event timetable', 'sage')),
        );
    }
}
