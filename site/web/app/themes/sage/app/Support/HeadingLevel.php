<?php

namespace App\Support;

/**
 * Heading levels for a block's section heading and the headings inside it.
 * A heading is the array a Heading field partial resolves to: text, level, style.
 */
class HeadingLevel
{
    /** A real heading is shown: it has text and isn't a "Not a heading" level. */
    public static function shows(mixed $heading): bool
    {
        return is_array($heading)
            && ! empty($heading['text'])
            && ($heading['level'] ?? 'h2') !== 'none';
    }

    /**
     * Level for headings inside a section: one below the section's heading when it
     * shows one, otherwise the section's own level (the headings take its place, so
     * the outline doesn't jump, e.g. from H2 straight to an H4).
     */
    public static function inside(mixed $sectionHeading): string
    {
        $level = is_array($sectionHeading) ? ($sectionHeading['level'] ?? 'h2') : 'h2';
        $level = preg_match('/^h[1-6]$/', $level) ? $level : 'h2';

        return static::shows($sectionHeading) ? 'h' . min(6, (int) substr($level, 1) + 1) : $level;
    }
}
