<?php

namespace App\Support;

/**
 * An archive (/events) can be a Modern Events Calendar instead of the filters and cards: when the page linked
 * to it in Theme Options → Archive pages contains an Events Calendar block, the archive shows that page's
 * content. The page is the one place editors build it (hero, intro, the calendar block).
 */
class ArchiveCalendar
{
    public const BLOCK = 'acf/events-calendar';

    /**
     * The published page whose content sits on a post type's archive (News: Settings → Reading's Posts page).
     */
    public static function linkedPageId(string $postType): ?int
    {
        $pageId = $postType === 'post'
            ? (int) get_option('page_for_posts')
            : (int) get_field("archive_page_{$postType}", 'option');

        return $pageId && get_post_status($pageId) === 'publish' ? $pageId : null;
    }

    /**
     * Does that page hold an Events Calendar block?
     */
    public static function pageHasCalendar(?int $pageId): bool
    {
        return $pageId && has_block(self::BLOCK, (string) get_post_field('post_content', $pageId));
    }

    /**
     * Is the archive being viewed one that shows a calendar?
     */
    public static function current(): bool
    {
        if (! is_post_type_archive()) {
            return false;
        }

        return static::pageHasCalendar(static::linkedPageId(ArchiveFilters::postType()));
    }
}
