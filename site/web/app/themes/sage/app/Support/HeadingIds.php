<?php

namespace App\Support;

/**
 * Slug ids for headings, so a section or list can point at its heading with
 * aria-labelledby without anyone writing an id by hand. Ids are the heading
 * text slugged, unique per request (repeats get -2, -3 ...) and the same on
 * every load of a page. Slugged the same way as PageMenu's anchors.
 */
class HeadingIds
{
    /** @var array<string, true> */
    protected static array $used = [];

    /**
     * Reserve and return the id for some heading text, or null when the text
     * has nothing to slug (empty, or only markup or an icon). Call it before
     * rendering to reference the id from a parent: pass it to <x-heading :id>.
     */
    public static function make(string $text): ?string
    {
        $plainText = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $slug = PageMenu::slugify($plainText);

        if ($slug === '') {
            return null;
        }

        $id = $slug;

        for ($suffix = 2; isset(static::$used[$id]); $suffix++) {
            $id = "{$slug}-{$suffix}";
        }

        return static::reserve($id);
    }

    /** Mark an id someone chose themselves as taken, so a generated one never repeats it. */
    public static function reserve(string $id): string
    {
        static::$used[$id] = true;

        return $id;
    }

    public static function reset(): void
    {
        static::$used = [];
    }
}
