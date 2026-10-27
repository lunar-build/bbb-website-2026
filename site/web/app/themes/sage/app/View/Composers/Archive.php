<?php

namespace App\View\Composers;

use App\Support\ArchiveFilters;
use App\Support\ContentHero;
use App\Support\PostCard;
use Roots\Acorn\View\Composer;

class Archive extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'archive',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        $postType = ArchiveFilters::postType();
        $content = $this->linkedContent($this->linkedPageId($postType));
        $breadcrumbs = $this->breadcrumbs($postType);

        return [
            'hero' => $content['hero'] ?: ContentHero::fallback(
                is_home() ? get_the_title(get_option('page_for_posts')) : get_the_archive_title(),
                $breadcrumbs,
            ),
            'body' => $content['body'],
            'filterGroups' => ArchiveFilters::groups($postType),
            'formAction' => ArchiveFilters::baseUrl($postType),
            'resultsHeading' => get_post_type_object($postType)->labels->name ?? '',
            'foundPosts' => (int) $GLOBALS['wp_query']->found_posts,
            'cards' => array_map(fn($post) => PostCard::fromPost($post, 'h4'), $GLOBALS['wp_query']->posts),
            'paginationLinks' => pagination_links(),
        ];
    }

    /**
     * The page whose content (Image Hero + intro) sits above this archive.
     */
    protected function linkedPageId(string $postType): ?int
    {
        $pageId = $postType === 'post'
            ? (int) get_option('page_for_posts')
            : (int) get_field("archive_page_{$postType}", 'option');

        return $pageId && get_post_status($pageId) === 'publish' ? $pageId : null;
    }

    /**
     * Blocks inside the linked page (Image Hero's breadcrumbs) read the
     * global post — point it at the page while rendering, not the archive's
     * first result.
     *
     * @return array{hero: string|null, body: string}
     */
    protected function linkedContent(?int $pageId): array
    {
        if (! $pageId) {
            return ['hero' => null, 'body' => ''];
        }

        global $post;

        $original = $post;
        $post = get_post($pageId);
        setup_postdata($post);

        $content = ContentHero::forPost($pageId);

        $post = $original;
        $original ? setup_postdata($original) : wp_reset_postdata();

        return $content;
    }

    /**
     * Fallback trail for when there's no linked page's Image Hero to show one.
     */
    protected function breadcrumbs(string $postType): array
    {
        return [
            ['label' => __('Home', 'sage'), 'url' => home_url('/')],
            ['label' => get_post_type_object($postType)->labels->name ?? get_the_archive_title()],
        ];
    }
}
