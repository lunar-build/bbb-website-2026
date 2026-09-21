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
            ->filter(fn($composer) => $composer instanceof Block)
            ->map(fn(Block $block) => [
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
     *
     * Blocks with more than one visually distinct fixture (e.g. CtaBanner's
     * layout variants) can expose an optional `$examples` property — an
     * array of `['Label' => $exampleOverrides]` — to render each variant
     * stacked in the library instead of just the default `$example`. This
     * is opt-in: blocks without `$examples` render exactly as before.
     */
    protected function render(Block $block): string
    {
        if (empty($block->examples)) {
            return $this->renderVariant($block);
        }

        $baseExample = $block->example;

        $html = collect($block->examples)
            ->map(function ($overrides, $label) use ($block, $baseExample) {
                $block->example = array_merge($baseExample, $overrides);
                $variantHtml = $this->renderVariant($block);
                $block->example = $baseExample;

                return sprintf(
                    '<div class="c-pattern-library__variant"><p class="c-pattern-library__variant-label">%s</p>%s</div>',
                    esc_html($label),
                    $variantHtml,
                );
            })
            ->implode('');

        return $html;
    }

    /**
     * Render a single pass of a block's Blade view using its current
     * `$example`/`$exampleContent` fixture data.
     */
    protected function renderVariant(Block $block): string
    {
        $content = method_exists($block, 'exampleContent')
            ? $block->exampleContent()
            : ($block->exampleContent ?? '');

        $html = $block->render([], $content, true);

        if ($content !== '') {
            $html = acf_replace_inner_blocks_in_block_content($content, $html);
        }

        return $this->injectPreviewSpacingStyle($html, $block);
    }

    /**
     * Standalone preview rendering (`$block->preview = true`) skips ACF
     * Composer's normal `WP_Block_Supports::apply_block_supports()` call
     * (there's no real `WP_Block` context to read from outside a genuine
     * `render_block()` pass), so a block's default spacing support never
     * makes it into the rendered `style` attribute here — unlike the front
     * end and real editor canvas, which both go through that real pipeline.
     * Recreate just the padding declarations by hand from the block's own
     * `$spacing` default, using the same style engine WP_Block_Supports
     * itself calls internally, and merge them onto the root wrapper.
     */
    protected function injectPreviewSpacingStyle(string $html, Block $block): string
    {
        $spacing = array_filter($block->spacing ?? []);

        if (empty($spacing)) {
            return $html;
        }

        $css = wp_style_engine_get_styles(['spacing' => $spacing])['css'] ?? '';

        if ($css === '') {
            return $html;
        }

        return preg_replace_callback('/<section\b[^>]*>/', function ($matches) use ($css) {
            $tag = $matches[0];

            if (preg_match('/style="([^"]*)"/', $tag, $styleMatch)) {
                $merged = rtrim($styleMatch[1], ';') . ';' . $css;

                return str_replace($styleMatch[0], 'style="' . esc_attr($merged) . '"', $tag);
            }

            return substr($tag, 0, -1) . ' style="' . esc_attr($css) . '">';
        }, $html, 1);
    }

    /**
     * Every Blade `@props` component under resources/views/components/, for
     * the dev-only "Components" section of the pattern library (see
     * `resources/views/partials/pattern-library-styles.blade.php` for the
     * hand-maintained counterpart covering non-component style primitives
     * like buttons/type scale, which have no file to discover).
     *
     * A component appears here automatically — a matching example partial
     * at resources/views/components/examples/{name}.blade.php is what fills
     * in its rendered preview; without one it still lists, just with a
     * "no example yet" note, so the gap stays visible.
     */
    public function components(): array
    {
        $path = get_theme_file_path('resources/views/components');

        return collect(glob("{$path}/*.blade.php"))
            ->map(fn($file) => basename($file, '.blade.php'))
            ->map(fn($name) => [
                'name' => $name,
                'html' => view()->exists("components.examples.{$name}")
                    ? view("components.examples.{$name}")->render()
                    : null,
            ])
            ->sortBy('name')
            ->values()
            ->all();
    }
}
