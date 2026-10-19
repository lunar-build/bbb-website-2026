<?php

namespace App\TermTimetable;

use WP_Post;

/**
 * Warns before a class that term timetables use is trashed or deleted, and
 * after it's trashed. Trashing takes its sessions off What's on (restoring
 * brings them back); deleting permanently loses its dates, and the terms
 * then need another class picked for those sessions.
 */
class ClassDeletionWarnings
{
    public static function boot(): void
    {
        add_filter('post_row_actions', [static::class, 'rowActions'], 20, 2);
        add_action('admin_footer-edit.php', [static::class, 'bulkConfirm']);
        add_action('admin_notices', [static::class, 'trashedNotice']);
    }

    /** "Term 1" and “Half Term”, for messages. */
    protected static function termNames(int $event): string
    {
        return implode(', ', array_map(fn(WP_Post $t) => '“' . html_entity_decode(get_the_title($t), ENT_QUOTES) . '”', ClassEditGuard::termsFor($event)));
    }

    public static function trashMessage(int $event): string
    {
        return sprintf(
            /* translators: 1: class name, 2: term names */
            __('“%1$s” is used in the term timetable(s) %2$s. Moving it to the Trash takes its sessions off the What\'s on timetable until it\'s restored. Continue?', 'sage'),
            html_entity_decode(get_the_title($event), ENT_QUOTES),
            static::termNames($event),
        );
    }

    public static function deleteMessage(int $event): string
    {
        return sprintf(
            /* translators: 1: class name, 2: term names */
            __('“%1$s” is used in the term timetable(s) %2$s. Deleting it permanently removes all its sessions, and those terms will show it as a missing class until another is picked in the term editor. This can\'t be undone. Continue?', 'sage'),
            html_entity_decode(get_the_title($event), ENT_QUOTES),
            static::termNames($event),
        );
    }

    /** A confirm() on the list screen's Trash and Delete Permanently links. */
    public static function rowActions(array $actions, WP_Post $post): array
    {
        if ($post->post_type !== 'mec-events' || ! ClassEditGuard::termsFor($post->ID)) {
            return $actions;
        }
        foreach (['trash' => static::trashMessage($post->ID), 'delete' => static::deleteMessage($post->ID)] as $key => $message) {
            if (isset($actions[$key])) {
                $actions[$key] = preg_replace('/<a /', '<a onclick="' . esc_attr('return confirm(' . wp_json_encode($message) . ');') . '" ', $actions[$key], 1);
            }
        }

        return $actions;
    }

    /** The same check for bulk Move to Trash / Delete Permanently, and Empty Trash. */
    public static function bulkConfirm(): void
    {
        if (get_current_screen()?->id !== 'edit-mec-events') {
            return;
        }
        $used = [];
        foreach (get_posts(['post_type' => 'mec-events', 'post_status' => ['publish', 'draft', 'trash', 'private', 'future'], 'numberposts' => -1, 'fields' => 'ids']) as $id) {
            if ($names = static::termNames($id)) {
                $used[$id] = html_entity_decode(get_the_title($id), ENT_QUOTES) . " ($names)";
            }
        }
        if (! $used) {
            return;
        }
        ?>
        <script>
        (function () {
          var used = <?php echo wp_json_encode($used); ?>;
          var form = document.getElementById('posts-filter');
          if (!form) return;
          form.addEventListener('submit', function (e) {
            var submitter = e.submitter || {};
            var action = submitter.name === 'delete_all' ? 'delete_all'
              : (form.querySelector('[name="action"]').value !== '-1' ? form.querySelector('[name="action"]').value : (form.querySelector('[name="action2"]') || {}).value);
            if (['trash', 'delete', 'delete_all'].indexOf(action) === -1) return;
            var ids = action === 'delete_all'
              ? Array.prototype.map.call(form.querySelectorAll('tr[id^="post-"]'), function (tr) { return tr.id.replace('post-', ''); })
              : Array.prototype.map.call(form.querySelectorAll('input[name="post[]"]:checked'), function (c) { return c.value; });
            var hit = ids.filter(function (id) { return used[id]; }).map(function (id) { return '• ' + used[id]; });
            if (!hit.length) return;
            var message = (action === 'trash'
              ? <?php echo wp_json_encode(__('These classes are used in term timetables. Moving them to the Trash takes their sessions off the What\'s on timetable until they\'re restored:', 'sage')); ?>
              : <?php echo wp_json_encode(__('These classes are used in term timetables. Deleting them permanently removes all their sessions, and the terms will need other classes picked. This can\'t be undone:', 'sage')); ?>)
              + '\n\n' + hit.join('\n') + '\n\n' + <?php echo wp_json_encode(__('Continue?', 'sage')); ?>;
            if (!window.confirm(message)) e.preventDefault();
          });
        })();
        </script>
        <?php
    }

    /** After trashing (list screen or block editor, which both land on ?trashed=1&ids=…). */
    public static function trashedNotice(): void
    {
        if (get_current_screen()?->id !== 'edit-mec-events' || empty($_GET['trashed']) || empty($_GET['ids'])) {
            return;
        }
        foreach (array_map('intval', explode(',', (string) $_GET['ids'])) as $id) {
            if (! $names = static::termNames($id)) {
                continue;
            }
            printf(
                '<div class="notice notice-warning"><p>%s</p></div>',
                esc_html(sprintf(
                    /* translators: 1: class name, 2: term names */
                    __('“%1$s” is in the Trash, so its sessions from %2$s are no longer on the What\'s on timetable. Restore it from the Trash (or use Undo above) to bring them back.', 'sage'),
                    html_entity_decode(get_the_title($id), ENT_QUOTES),
                    $names,
                )),
            );
        }
    }
}
