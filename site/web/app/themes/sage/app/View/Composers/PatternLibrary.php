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
     * Every registered block rendered with its `$example` fixture data. New blocks
     * appear automatically — see "Fixture data" in the build-acf-block skill.
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
     * Render a block's view standalone from its fixture data. An optional `$examples`
     * (`['Label' => $exampleOverrides]`) renders each variant stacked instead.
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

    /** One render pass using the block's current `$example`/`$exampleContent`. */
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
     * Standalone previews skip `WP_Block_Supports::apply_block_supports()` (no real
     * `WP_Block` context), so the default spacing support never reaches the `style`
     * attribute. Rebuild the padding from the block's `$spacing` with the same style
     * engine and merge it onto the root wrapper.
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
     * Every `@props` component in resources/views/components/ for the dev-only
     * "Components" section (non-component primitives are hand-maintained in
     * partials/pattern-library-styles.blade.php). Previews come from
     * components/examples/{name}.blade.php; without one it lists a "no example yet" note.
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
