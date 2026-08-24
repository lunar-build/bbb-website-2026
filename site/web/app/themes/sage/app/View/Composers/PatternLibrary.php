<?php

namespace App\View\Composers;

use Log1x\AcfComposer\Block;
use Roots\Acorn\View\Composer;

class PatternLibrary extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'template-pattern-library',
    ];

    /**
     * Every registered block, rendered with its fixture (`$example`) data
     * standing in for real ACF field values, for display on the pattern
     * library page. New blocks appear here automatically — see the
     * "Fixture data" section of the build-acf-block skill for the
     * per-block convention this relies on ($description + $example +
     * $exampleContent for InnerBlocks-based blocks).
     */
    public function blocks(): array
    {
        return collect(app('AcfComposer')->composers())
            ->flatten()
            ->filter(fn ($composer) => $composer instanceof Block)
            ->map(fn (Block $block) => [
                'name' => $block->getName(),
                'slug' => $block->slug,
                'description' => $block->getDescription(),
                'icon' => $block->getIcon(),
                'html' => $this->render($block),
            ])
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Render a block's Blade view standalone using its fixture data, with
     * no live post/ACF context required.
     */
    protected function render(Block $block): string
    {
        $content = $block->exampleContent ?? '';

        $html = $block->render([], $content, true);

        if ($content !== '') {
            $html = acf_replace_inner_blocks_in_block_content($content, $html);
        }

        return $html;
    }
}
