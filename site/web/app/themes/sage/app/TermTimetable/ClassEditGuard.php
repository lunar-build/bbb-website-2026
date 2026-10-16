<?php

namespace App\TermTimetable;

use WP_Post;

/**
 * On a class's MEC edit screen: says which term timetables manage its
 * sessions, and stops an out-of-date copy of the screen undoing them.
 *
 * MEC's own save writes back every date the page loaded with. If a term was
 * updated after the page opened, that would quietly restore the old times.
 * The screen carries the class's MecSync timestamp from when it loaded; if
 * it has moved on by the time it's saved, the dates are put back as the term
 * left them (everything else on the screen still saves) and a notice says so.
 */
class ClassEditGuard
{
    public const FIELD = 'bbb_term_synced_at';

    public const NOTICE = 'bbb_term_stale_save';

    /** MEC's date fields, restored together after a stale save. */
    protected const DATE_META = [
        'mec_start_date', 'mec_start_time_hour', 'mec_start_time_minutes', 'mec_start_time_ampm', 'mec_start_day_seconds',
        'mec_end_date', 'mec_end_time_hour', 'mec_end_time_minutes', 'mec_end_time_ampm', 'mec_end_day_seconds',
        'mec_date', 'mec_allday', 'mec_hide_time', 'mec_hide_end_time',
        'mec_repeat_status', 'mec_repeat_type', 'mec_repeat_interval', 'mec_repeat_end', 'mec_repeat_end_at_date',
        'mec_repeat_end_at_occurrences', 'mec_certain_weekdays', 'mec_advanced_days', 'mec_in_days', 'mec_not_in_days',
        '_event_undated',
    ];

    public static function boot(): void
    {
        add_action('add_meta_boxes_mec-events', [static::class, 'metaBox']);
        add_action('enqueue_block_editor_assets', [static::class, 'editorNotice']);
        // save_post_{type} runs before MEC's save_post handler.
        add_action('save_post_mec-events', [static::class, 'guard'], 9);
        add_action('rest_api_init', fn() => register_rest_route(RestController::NS, '/term-guard/(?P<id>\\d+)', [
            'methods' => 'POST',
            'permission_callback' => fn($request) => current_user_can('edit_post', (int) $request['id']),
            'callback' => fn($request) => ['kept' => static::takeNotice((int) $request['id'])],
        ]));
    }

    /** Published terms that generated sessions for this class. */
    public static function termsFor(int $event): array
    {
        return array_values(array_filter(
            get_posts(['post_type' => TermTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1]),
            fn(WP_Post $term) => ! empty(((array) get_post_meta($term->ID, MecSync::TERM_GENERATED, true))[$event]),
        ));
    }

    /** Sidebar box: the terms, and the load-time stamp the guard compares on save. */
    public static function metaBox(WP_Post $post): void
    {
        if (! static::termsFor($post->ID)) {
            return;
        }
        add_meta_box('bbb-term-timetables', __('Term timetables', 'sage'), function (WP_Post $post) {
            printf('<input type="hidden" name="%s" value="%s">', esc_attr(static::FIELD), esc_attr((string) get_post_meta($post->ID, MecSync::SYNCED_AT, true)));
            echo '<p>' . esc_html__('This class\'s weekly sessions come from:', 'sage') . '</p><ul>';
            foreach (static::termsFor($post->ID) as $term) {
                printf('<li><a href="%s">%s</a></li>', esc_url(TermTimetable::editUrl($term->ID)), esc_html(get_the_title($term)));
            }
            echo '</ul><p>' . esc_html__('Change its times there. Dates added here by hand are kept.', 'sage') . '</p>';
        }, 'mec-events', 'side', 'high');
    }

    /** The same message as an editor notice, plus the result of a stale save. */
    public static function editorNotice(): void
    {
        $post = get_post();
        if (! $post || $post->post_type !== 'mec-events') {
            return;
        }
        $notices = [];
        if ($terms = static::termsFor($post->ID)) {
            $notices[] = ['info', sprintf(
                /* translators: %s: term names */
                __('This class\'s sessions are set by the term timetable%s %s. Change its weekly times there, not under Event Repeating. If a term is updated while this page is open, reload this page before saving.', 'sage'),
                count($terms) > 1 ? 's' : '',
                implode(', ', array_map(fn($t) => '“' . get_the_title($t) . '”', $terms)),
            )];
        }
        // The block editor follows the meta-box save's redirect (?message=…) in the
        // background; only a page someone actually opens should use up the notice.
        if (! isset($_GET['message']) && static::takeNotice($post->ID)) {
            $notices[] = ['warning', static::keptMessage()];
        }
        $js = '';
        foreach ($notices as [$status, $message]) {
            $js .= sprintf('wp.data.dispatch("core/notices").createNotice(%s, %s, { isDismissible: true });', wp_json_encode($status), wp_json_encode($message));
        }
        if (static::termsFor($post->ID)) {
            // After a save, ask whether the guard kept the term's dates, and say so straight away.
            $js .= sprintf(
                'var saving = false; wp.data.subscribe(function () { var now = wp.data.select("core/edit-post").isSavingMetaBoxes(); if (saving && !now) { wp.apiFetch({ path: %s, method: "POST" }).then(function (r) { if (r.kept) wp.data.dispatch("core/notices").createNotice("warning", %s, { isDismissible: true }); }); } saving = now; });',
                wp_json_encode('/' . RestController::NS . '/term-guard/' . $post->ID),
                wp_json_encode(static::keptMessage()),
            );
        }
        if ($js) {
            wp_add_inline_script('wp-notices', "wp.domReady(function(){ $js });");
        }
    }

    protected static function keptMessage(): string
    {
        return __('A term timetable changed this class\'s sessions after you opened this page, so its dates were kept as the term set them. Everything else you changed was saved. Reload the page to see the current dates.', 'sage');
    }

    /** Whether the guard kept this class's dates since the last check (and clear it). */
    public static function takeNotice(int $postId): bool
    {
        $key = static::NOTICE . '_' . $postId;
        $kept = (bool) get_transient($key);
        delete_transient($key);

        return $kept;
    }

    /**
     * Before MEC saves: if the screen's stamp is out of date, snapshot the
     * dates and restore them once MEC (save_post, 10) has written its copy.
     */
    public static function guard(int $postId): void
    {
        if (! isset($_POST[static::FIELD], $_POST['mec']) || wp_is_post_revision($postId)) {
            return;
        }
        if ((string) wp_unslash($_POST[static::FIELD]) === (string) get_post_meta($postId, MecSync::SYNCED_AT, true)) {
            return;
        }

        global $wpdb;
        $meta = [];
        foreach (static::DATE_META as $key) {
            $meta[$key] = metadata_exists('post', $postId, $key) ? get_post_meta($postId, $key, true) : null;
        }
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mec_events WHERE post_id = %d", $postId), ARRAY_A);

        add_action('save_post', function ($savedId) use ($postId, $meta, $row, $wpdb) {
            if ((int) $savedId !== $postId) {
                return;
            }
            foreach ($meta as $key => $value) {
                $value === null ? delete_post_meta($postId, $key) : update_post_meta($postId, $key, $value);
            }
            if ($row) {
                $wpdb->update($wpdb->prefix . 'mec_events', array_diff_key($row, ['id' => 1]), ['post_id' => $postId]);
            }
            MecSync::reschedule($postId);
            set_transient(static::NOTICE . '_' . $postId, 1, 10 * MINUTE_IN_SECONDS);
        }, 20);
    }
}
