<?php

namespace App\Support;

use Illuminate\View\ComponentAttributeBag;

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
     * Blocks that count as a page's hero when placed first.
     */
    public const HERO_BLOCKS = ['acf/image-hero', 'acf/text-hero', 'acf/video-hero'];

    /**
     * Split a post's content into an optional leading hero block (rendered
     * full-bleed, outside the Sticky Nav template's two-column grid) and the
     * rest of the content (rendered inside the grid's content column).
     *
     * Editors create the hero by placing an Image, Text or Video Hero block
     * as the very first block — no dedicated hero field on the template, so
     * it works exactly like every other page's hero already does. If the
     * first block isn't a hero, there's no hero and the full content renders
     * in the content column as normal (templates then use fallback()).
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

        if (in_array($blocks[0]['blockName'] ?? null, self::HERO_BLOCKS, true)) {
            $heroBlock = array_shift($blocks);
        }

        return static::$cache[$postId] = [
            'hero' => $heroBlock ? apply_filters('the_content', serialize_block($heroBlock)) : null,
            'body' => apply_filters('the_content', serialize_blocks($blocks)),
        ];
    }

    /**
     * A Text Hero for pages/archives with no hero block of their own — the
     * title plus breadcrumbs, so the heading gets the same spacing as any
     * other hero instead of butting up against the header.
     */
    public static function fallback(string $title, array $breadcrumbs): string
    {
        return view('blocks.text-hero', [
            'attributes' => new ComponentAttributeBag([
                'class' => 'alignfull',
                'style' => 'padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large);',
            ]),
            'breadcrumbs' => $breadcrumbs,
            'heading' => ['text' => html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES), 'level' => 'h1', 'style' => 'match'],
            'intro' => ['text' => '', 'style' => 'standfirst'],
        ])->render();
    }
}
