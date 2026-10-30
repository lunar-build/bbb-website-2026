<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use App\Fields\Copy;
use App\Fields\Heading;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class InstagramFeed extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Instagram Feed';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'instagram-feed';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A heading and intro above a Smash Balloon Instagram Feed Pro feed.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'media';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'instagram';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'instagram',
        'social',
        'feed',
        'smash balloon',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['post', 'page'];

    /**
     * The parent block type allow list.
     *
     * @var array
     */
    public $parent = [];

    /**
     * The ancestor block type allow list.
     *
     * @var array
     */
    public $ancestor = [];

    /**
     * The default block mode.
     *
     * @var string
     */
    public $mode = 'auto';

    /**
     * The default block alignment.
     *
     * @var string
     */
    public $align = '';

    /**
     * The default block text alignment.
     *
     * @var string
     */
    public $align_text = '';

    /**
     * The default block content alignment.
     *
     * @var string
     */
    public $align_content = '';

    /**
     * The default block spacing.
     *
     * @var array
     */
    public $spacing = [
        'padding' => [
            'top' => 'var:preset|spacing|large',
            'bottom' => 'var:preset|spacing|large',
        ],
        'margin' => null,
    ];

    /**
     * The supported block features.
     *
     * @var array
     */
    public $supports = [
        'align' => true,
        'align_text' => false,
        'align_content' => false,
        'full_height' => false,
        'anchor' => false,
        'mode' => true,
        'multiple' => true,
        'jsx' => true,
        'color' => [
            'background' => true,
            'text' => false,
            'gradients' => false,
        ],
        'spacing' => [
            'padding' => ['top', 'bottom'],
            'margin' => false,
        ],
    ];

    /**
     * The block styles.
     *
     * @var array
     */
    public $styles = [];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'heading_text' => 'Love cycling? Tag yourself #ibikeitilikeit',
        'heading_level' => 'h2',
        'heading_style' => 'h1',
        'intro_text' => 'Tag your photos with #betterbybike and #ibikeitilikeit for a chance to be featured here.',
        'intro_style' => 'standfirst',
        'feed_id' => '',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'intro' => $this->intro(),
            'feedShortcode' => $this->feedShortcode(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('instagram_feed');

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Heading',
            'default_level' => 'h2',
            'default_style' => 'h1',
        ]);

        $fields->addPartial(Copy::class, [
            'name' => 'intro',
            'label' => 'Intro text',
            'default_style' => 'standfirst',
        ]);

        $fields->addNumber('feed_id', [
            'label' => 'Feed ID',
            'instructions' => 'The feed number from Instagram Feed → All Feeds. Leave empty to use the plugin\'s default feed.',
            'min' => 1,
            'step' => 1,
        ]);

        return $fields->build();
    }

    /**
     * Retrieve the heading text/level/style. Empty text is valid — the heading is optional.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => $this->resolveField('heading_text', ''),
            'level' => $this->resolveField('heading_level', 'h2'),
            'style' => $this->resolveField('heading_style', 'h1'),
        ];
    }

    /**
     * Retrieve the intro text/style. Empty text is valid — the intro is optional.
     *
     * @return array
     */
    public function intro()
    {
        return [
            'text' => $this->resolveField('intro_text', ''),
            'style' => $this->resolveField('intro_style', 'standfirst'),
        ];
    }

    /**
     * The plugin's shortcode for the chosen feed, or empty when Instagram Feed
     * Pro isn't active (it is a separate licensed plugin, so not every
     * environment has it).
     *
     * @return string
     */
    public function feedShortcode()
    {
        if (! shortcode_exists('instagram-feed')) {
            return '';
        }

        $feedId = (int) $this->resolveField('feed_id', 0);

        return $feedId > 0 ? sprintf('[instagram-feed feed=%d]', $feedId) : '[instagram-feed]';
    }

    /**
     * Assets enqueued with 'enqueue_block_assets' when rendering the block.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
