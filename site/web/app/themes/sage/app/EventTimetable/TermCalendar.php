<?php

namespace App\EventTimetable;

/**
 * Keeps one MEC shortcode (a monthly calendar) in step with each published timetable, so a term's
 * calendar can be picked in the Events Calendar block without building it by hand in MEC.
 *
 * MEC's skin and filter settings live in the database, so this is what makes the calendar
 * reproducible on every environment. Saving a timetable overwrites those settings.
 */
class TermCalendar
{
    public const POST_TYPE = 'mec_calendars';

    /** On the timetable: the ID of the shortcode generated for it. */
    public const TIMETABLE_CALENDAR = '_timetable_calendar_id';

    /** On the shortcode: the term's first and last day (Y-m-d). The Events Calendar block caps the month navigation to them. */
    public const FIRST_DAY = '_term_calendar_first_day';

    public const LAST_DAY = '_term_calendar_last_day';

    /**
     * Create or update the timetable's shortcode if it is published; otherwise trash it
     * (a draft or trashed timetable) or delete it (a deleted timetable).
     */
    public static function sync(int $timetableId, bool $deleting = false): void
    {
        $calendarId = (int) get_post_meta($timetableId, static::TIMETABLE_CALENDAR, true);
        $hasCalendar = $calendarId && get_post_type($calendarId) === static::POST_TYPE;

        if ($deleting) {
            if ($hasCalendar) {
                wp_delete_post($calendarId, true);
            }

            return;
        }

        $schedule = Schedule::normalise(get_post_meta($timetableId, EventTimetable::META, true));
        $hasDates = preg_match(Schedule::DATE, $schedule['start']) && preg_match(Schedule::DATE, $schedule['end']);

        if (get_post_status($timetableId) !== 'publish' || ! $hasDates) {
            if ($hasCalendar) {
                wp_trash_post($calendarId);
            }

            return;
        }

        $postArguments = ['post_title' => static::title($timetableId), 'post_status' => 'publish', 'post_type' => static::POST_TYPE];
        if ($hasCalendar) {
            wp_update_post(['ID' => $calendarId] + $postArguments);
        } else {
            $calendarId = (int) wp_insert_post($postArguments);
            update_post_meta($timetableId, static::TIMETABLE_CALENDAR, $calendarId);
        }

        // After the post is saved: MEC's own save handler writes defaults to the same meta keys.
        foreach (static::meta($schedule) as $key => $value) {
            update_post_meta($calendarId, $key, $value);
        }
    }

    public static function title(int $timetableId): string
    {
        /* translators: %s: timetable name */
        return sprintf(__('Term calendar: %s', 'sage'), get_the_title($timetableId));
    }

    /**
     * A term's first and last day, if the shortcode was generated from a timetable.
     *
     * @return array{first: string, last: string}|null
     */
    public static function range(int $calendarId): ?array
    {
        $first = (string) get_post_meta($calendarId, static::FIRST_DAY, true);
        $last = (string) get_post_meta($calendarId, static::LAST_DAY, true);

        return $first && $last ? ['first' => $first, 'last' => $last] : null;
    }

    /**
     * The shortcode's meta, in the shape MEC itself saves: a monthly view in the Modern style
     * starting on the term's first day, limited to the timetable's category, with a search form
     * (category dropdown and text search). There is no month filter: it would let visitors jump
     * past the term, and a term is only a few weeks long.
     *
     * @param  array{start: string, end: string, category: int}  $schedule
     */
    public static function meta(array $schedule): array
    {
        $category = $schedule['category'] ? (string) $schedule['category'] : '';
        $disabledFilter = ['type' => '0'];

        return [
            'skin' => 'monthly_view',
            'sk-options' => ['monthly_view' => [
                'style' => 'modern',
                'start_date_type' => 'date',
                'start_date' => $schedule['start'],
                'limit' => '',
                'include_local_time' => '0',
                'next_previous_button' => '1',
                'display_label' => '0',
                'reason_for_cancellation' => '0',
                'display_organizer' => '0',
                'activate_first_date' => '0',
                'activate_current_day' => '1',
                'display_all' => '0',
                'booking_button' => '0',
                'custom_data' => '0',
                'detailed_time' => '1',
                'sed_method' => '0',
                'image_popup' => '0',
                'image_size' => 'default',
            ]],
            'sf-options' => ['monthly_view' => [
                'category' => ['type' => 'dropdown'],
                'location' => $disabledFilter,
                'organizer' => $disabledFilter,
                'speaker' => $disabledFilter,
                'tag' => $disabledFilter,
                'label' => $disabledFilter,
                'address_search' => ['type' => '0', 'placeholder' => '', 'radius' => ''],
                'event_cost' => $disabledFilter,
                'month_filter' => $disabledFilter,
                'time_filter' => $disabledFilter,
                'text_search' => ['type' => 'text_input', 'placeholder' => ''],
            ]],
            'sf_status' => 1,
            'sf_display_label' => 0,
            'sf_reset_button' => 0,
            'sf_refine' => 0,
            'sf_dropdown_method' => 1,
            'entity_type_filter' => 'all',
            'show_only_one_occurrence' => 0,
            'show_past_events' => 1,
            'show_only_past_events' => 0,
            'show_ongoing_events' => 0,
            'show_only_ongoing_events' => 0,
            'category' => $category,
            'ex_category' => '',
            static::FIRST_DAY => $schedule['start'],
            static::LAST_DAY => $schedule['end'],
        ];
    }
}
