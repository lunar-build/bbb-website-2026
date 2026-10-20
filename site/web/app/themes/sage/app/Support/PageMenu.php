<?php

namespace App\Support;

class PageMenu
{
    /**
     * Build the sticky page menu's `items` array (for <x-sticky-page-menu>)
     * from a post's top-level H2 headings.
     *
     * Prefers the Gutenberg heading block's built-in "HTML anchor" field
     * when an editor has set one (core renders that as the heading's `id`
     * automatically, no extra work needed), falling back to an auto-slug of
     * the heading text otherwise — see the matching `render_block_core/heading`
     * filter in app/filters.php, which injects that same fallback slug as
     * the rendered heading's `id` so the links here actually resolve.
     */
    public static function forPost(?int $postId = null): array
    {
        $postId = $postId ?: get_the_ID();

        if (! $postId) {
            return [];
        }

        $content = get_post_field('post_content', $postId);

        if (! $content) {
            return [];
        }

        return collect(static::headingBlocks(parse_blocks($content)))
            ->map(fn ($block) => [
                'label' => trim(wp_strip_all_tags($block['innerHTML'] ?? '')),
                'anchor' => static::anchorFor($block),
            ])
            ->filter(fn ($item) => $item['label'] !== '' && $item['anchor'] !== '')
            ->map(fn ($item) => [
                'label' => $item['label'],
                'url' => '#'.$item['anchor'],
                'active' => false,
            ])
            ->values()
            ->all();
    }

    /**
     * Recursively find top-level (H2) core/heading blocks, including ones
     * nested inside a group/columns/etc.
     */
    protected static function headingBlocks(array $blocks): array
    {
        $headings = [];

        foreach ($blocks as $block) {
            if (($block['blockName'] ?? null) === 'core/heading' && (int) ($block['attrs']['level'] ?? 2) === 2) {
                $headings[] = $block;
            }

            if (! empty($block['innerBlocks'])) {
                $headings = array_merge($headings, static::headingBlocks($block['innerBlocks']));
            }
        }

        return $headings;
    }

    /**
     * The anchor slug for a heading block: the editor's manual "HTML anchor"
     * if set, otherwise an auto-slug of the heading text.
     */
    public static function anchorFor(array $block): string
    {
        if (! empty($block['attrs']['anchor'])) {
            return $block['attrs']['anchor'];
        }

        return static::slugify(wp_strip_all_tags($block['innerHTML'] ?? ''));
    }

    /**
     * Plain-PHP slugify (no WordPress functions), so this can be unit
     * tested without a WordPress bootstrap — matches sanitize_title()'s
     * ASCII behaviour closely enough for heading text.
     */
    public static function slugify(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug ?? '', '-');

        return $slug;
    }
}
