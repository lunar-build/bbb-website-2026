<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Table extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Table';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'data-table';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A data table with author-defined columns and rows — not limited to any fixed column count.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'text';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'editor-table';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'table',
        'data',
        'rows',
        'columns',
        'contacts',
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
            'background' => false,
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
     * The block preview example data — council highways contact numbers,
     * matching the Figma "Table" reference design. A 2-column table is just
     * one valid configuration of this block, not a fixed shape.
     *
     * @var array
     */
    public $example = [
        'caption' => 'Highways contact numbers by council area',
        'first_column_is_header' => true,
        'columns' => [
            ['heading' => 'Where is the problem?'],
            ['heading' => 'For an URGENT report'],
        ],
        'rows' => [
            [
                'cells' => [
                    ['content' => '<p>Bristol City Council</p>'],
                    ['content' => '<p><a href="tel:01179222100">0117 922 2100</a><br>During office hours</p><p><a href="tel:01179222050">0117 922 2050</a><br>Out of hours emergency</p>'],
                ],
            ],
            [
                'cells' => [
                    ['content' => '<p>Bath &amp; NE Somerset Council</p>'],
                    ['content' => '<p><a href="tel:01225394041">01225 394041</a><br>During office hours</p><p><a href="tel:01225477477">01225 477477</a><br>Out of hours emergency</p>'],
                ],
            ],
            [
                'cells' => [
                    ['content' => '<p>North Somerset Council</p>'],
                    ['content' => '<p><a href="tel:01275888802">01275 888802</a><br>During office hours</p><p><a href="tel:01934622669">01934 622669</a><br>Out of hours emergency</p>'],
                ],
            ],
            [
                'cells' => [
                    ['content' => '<p>South Gloucestershire Council</p>'],
                    ['content' => '<p><a href="tel:01454868000">01454 868000</a><br>During office hours</p><p><a href="tel:01454868009">01454 868009</a><br>Out of hours emergency</p>'],
                ],
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'caption' => $this->caption(),
            'firstColumnIsHeader' => $this->firstColumnIsHeader(),
            'columns' => $this->columns(),
            'rows' => $this->rows(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('table');

        $fields
            ->addTab('Content')
                ->addText('caption', [
                    'label' => 'Caption',
                    'instructions' => 'Names the table for screen reader users. Not shown visually.',
                    'required' => true,
                ])
                ->addTrueFalse('first_column_is_header', [
                    'label' => 'First column is a row heading',
                    'instructions' => 'On — each row\'s first cell is announced as that row\'s heading (e.g. a council/item name). Off — all cells are treated as plain data.',
                    'default_value' => 1,
                    'ui' => true,
                ])
            ->addTab('Columns')
                ->addRepeater('columns', [
                    'label' => 'Columns',
                    'instructions' => 'One entry per column heading, left to right.',
                    'button_label' => 'Add column',
                    'min' => 1,
                    'layout' => 'table',
                ])
                    ->addText('heading', [
                        'label' => 'Heading',
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Rows')
                ->addRepeater('rows', [
                    'label' => 'Rows',
                    'instructions' => 'Each row needs the same number of cells as there are columns above.',
                    'button_label' => 'Add row',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addRepeater('cells', [
                        'label' => 'Cells',
                        'button_label' => 'Add cell',
                        'min' => 1,
                        'layout' => 'table',
                    ])
                        ->addWysiwyg('content', [
                            'label' => 'Content',
                            'tabs' => 'visual',
                            'media_upload' => 0,
                            'toolbar' => 'basic',
                        ])
                    ->endRepeater()
                ->endRepeater()
        ;

        return $fields->build();
    }

    /**
     * Retrieve the caption.
     *
     * @return string
     */
    public function caption()
    {
        return get_field('caption') ?: $this->example['caption'];
    }

    /**
     * Retrieve whether the first column is a row heading.
     *
     * @return bool
     */
    public function firstColumnIsHeader()
    {
        $value = get_field('first_column_is_header');

        return $value !== null ? (bool) $value : $this->example['first_column_is_header'];
    }

    /**
     * Retrieve the columns.
     *
     * @return array
     */
    public function columns()
    {
        $rows = get_field('columns');

        if (! $rows) {
            $rows = $this->preview ? $this->example['columns'] : [];
        }

        return $rows;
    }

    /**
     * Retrieve the rows, with a decorative phone icon prefixed onto any
     * tel: link inside a cell's wysiwyg content.
     *
     * @return array
     */
    public function rows()
    {
        $rows = get_field('rows');

        if (! $rows) {
            $rows = $this->preview ? $this->example['rows'] : [];
        }

        return array_map(function ($row) {
            $row['cells'] = array_map(function ($cell) {
                $cell['content'] = $this->decorateTelLinks($cell['content']);

                return $cell;
            }, $row['cells']);

            return $row;
        }, $rows);
    }

    /**
     * Prefix a decorative phone icon onto every tel: link in a block of
     * cell HTML, matching the Figma design's phone icon next to each
     * number (node 10:4095).
     *
     * @param  string  $html
     * @return string
     */
    protected function decorateTelLinks($html)
    {
        return preg_replace(
            '/<a\s+href="tel:/',
            '<wa-icon name="phone" aria-hidden="true" class="c-table__icon"></wa-icon><a href="tel:',
            $html,
        );
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
