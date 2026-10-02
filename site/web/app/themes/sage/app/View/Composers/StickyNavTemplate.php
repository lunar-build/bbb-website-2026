<?php

namespace App\View\Composers;

use App\Support\ContentHero;
use App\Support\PageMenu;
use Roots\Acorn\View\Composer;

class StickyNavTemplate extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'template-sticky-nav',
    ];

    /**
     * The sticky page menu's items, built from this page's H2 headings.
     */
    public function pageMenuItems(): array
    {
        return PageMenu::forPost();
    }

    /**
     * The leading Image Hero block's rendered HTML, if the page has one as
     * its first block — rendered full-bleed, outside the two-column grid.
     */
    public function hero(): ?string
    {
        return ContentHero::forPost()['hero'];
    }

    /**
     * The rest of the page's content (everything after a leading hero
     * block, if any) — rendered inside the grid's content column.
     */
    public function body(): string
    {
        return ContentHero::forPost()['body'];
    }

    /**
     * Pagination links for a paginated page (`<!--nextpage-->`), matching
     * App\View\Composers\Post::pagination() — not reused directly since
     * this template renders its own content column instead of including
     * partials.content-page.
     */
    public function pagination(): string
    {
        return wp_link_pages([
            'echo' => 0,
            'before' => '<p>'.__('Pages:', 'sage'),
            'after' => '</p>',
        ]);
    }
}
