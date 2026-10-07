<?php

namespace App\Support;

class ContentHero
{
    /**
     * Per-post-ID cache so hero()/body() calls on the same request (the
     * composer calls forPost() once each) don't re-parse and re-filter the
     * content twice.
     *
     * @var array<int, array{hero: string|null, body: string}>
     */
    protected static array $cache = [];

    /**
     * Split a post's content into an optional leading hero block (rendered
     * full-bleed, outside the Sticky Nav template's two-column grid) and the
     * rest of the content (rendered inside the grid's content column).
     *
     * Editors create the hero by placing the existing "Image Hero" ACF
     * block (acf/image-hero) as the very first block — no dedicated hero
     * field on the template, so it works exactly like every other page's
     * hero already does. If the first block isn't an Image Hero, there's no
     * hero and the full content renders in the content column as normal.
     *
     * Both halves go through the full `the_content` filter chain (via
     * serialize_block/serialize_blocks + apply_filters, the same mechanism
     * the_content() itself uses) so nothing besides the hero split changes
     * versus rendering the whole post normally.
     *
     * @return array{hero: string|null, body: string}
     */
    public static function forPost(?int $postId = null): array
    {
        $postId = $postId ?: get_the_ID();

        if (! $postId) {
            return ['hero' => null, 'body' => ''];
        }

        if (isset(static::$cache[$postId])) {
            return static::$cache[$postId];
        }

        $blocks = array_values(array_filter(
            parse_blocks(get_post_field('post_content', $postId)),
            fn ($block) => $block['blockName'] !== null
        ));

        $heroBlock = null;

        if (($blocks[0]['blockName'] ?? null) === 'acf/image-hero') {
            $heroBlock = array_shift($blocks);
        }

        return static::$cache[$postId] = [
            'hero' => $heroBlock ? apply_filters('the_content', serialize_block($heroBlock)) : null,
            'body' => apply_filters('the_content', serialize_blocks($blocks)),
        ];
    }
}
