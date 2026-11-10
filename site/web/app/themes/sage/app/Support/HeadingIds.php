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

    /**
     * Give every heading in a page's HTML that has no id one, so nothing slips
     * through: raw <h1>s, core heading blocks, plugin markup. Ids already on the
     * page (any element) are kept and never repeated. A heading with no text
     * gets "heading", "heading-2" and so on.
     */
    public static function addMissing(string $html): string
    {
        if (! preg_match('/<h[1-6][\s>]/i', $html)) {
            return $html;
        }

        if (preg_match_all('/\sid=(["\'])(.*?)\1/s', $html, $existing)) {
            foreach ($existing[2] as $existingId) {
                static::reserve($existingId);
            }
        }

        return preg_replace_callback('/<h([1-6])(\s[^>]*)?>(.*?)<\/h\1>/is', function (array $heading) {
            $attributes = $heading[2] ?? '';

            if (preg_match('/\sid=/i', $attributes)) {
                return $heading[0];
            }

            $id = static::make($heading[3]) ?? static::make('heading');

            return "<h{$heading[1]} id=\"{$id}\"{$attributes}>{$heading[3]}</h{$heading[1]}>";
        }, $html) ?? $html;
    }

    public static function reset(): void
    {
        static::$used = [];
    }
}
