<?php

namespace App\TermTimetable;

use Illuminate\Support\Facades\Vite;

/** The React term editor's admin page (resources/js/admin/term-timetable). */
class AdminPage
{
    public const SCREEN = 'admin_page_' . TermTimetable::PAGE;

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
        $termId = (int) ($_GET['term'] ?? 0);
        $term = $termId ? get_post($termId) : null;
        if ($term && $term->post_type !== TermTimetable::POST_TYPE) {
            $termId = 0;
        }

        // Standard post locking: claim the term, or say who already has it open.
        $lockedBy = null;
        if ($termId) {
            $holder = wp_check_post_lock($termId);
            $lockedBy = $holder ? get_userdata($holder)?->display_name : null;
            if (! $holder) {
                wp_set_post_lock($termId);
            }
        }

        $default = get_term_by('slug', 'bristol-cycling-centre', 'mec_category');

        printf('<script>window.bbbTermTimetable = %s;</script>', wp_json_encode([
            'termId' => $termId,
            'listUrl' => admin_url('edit.php?post_type=' . TermTimetable::POST_TYPE),
            'editUrl' => TermTimetable::editUrl(),
            'defaultCategory' => $default ? $default->term_id : 0,
            'lockedBy' => $lockedBy,
            'canPublish' => current_user_can('publish_posts'),
        ]));
        echo Vite::withEntryPoints(['resources/js/admin/term-timetable/index.jsx'])->toHtml();
    }

    public static function render(): void
    {
        // wp-header-end: WordPress moves admin notices here, above the editor rather than inside it.
        printf(
            '<div class="wrap bbb-tt-wrap"><h1 class="wp-heading-inline">%s</h1><hr class="wp-header-end"><div id="bbb-term-timetable"></div></div>',
            esc_html(isset($_GET['term']) ? __('Edit term timetable', 'sage') : __('New term timetable', 'sage')),
        );
    }
}
