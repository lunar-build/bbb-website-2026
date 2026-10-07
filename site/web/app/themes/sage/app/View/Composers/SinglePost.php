<?php

namespace App\View\Composers;

use App\Blocks\PostHero;
use App\Support\RelatedPosts;
use Roots\Acorn\View\Composer;

class SinglePost extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.content-single-post',
    ];

    /**
     * Retrieve the hero data (date, title, permalink, categories) — reuses
     * the Post Hero block's own accessors so both stay in sync.
     */
    public function hero(): array
    {
        $postHero = collect(app('AcfComposer')->composers())
            ->flatten()
            ->first(fn($composer) => $composer instanceof PostHero);

        return [
            'date' => $postHero->date(),
            'dateIso' => $postHero->dateIso(),
            'title' => $postHero->title(),
            'permalink' => $postHero->permalink(),
            'categories' => $postHero->categories(),
        ];
    }

    /**
     * Retrieve the "Also read" related posts.
     */
    public function relatedPosts(): array
    {
        return RelatedPosts::latest(get_the_ID());
    }
}
