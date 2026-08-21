<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.scss');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'footer_navigation' => __('Footer Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

// enqueue web awesome
add_action('wp_enqueue_scripts', function () {});

/**
 * Relabel the built-in "Posts" post type as "News".
 *
 * @return void
 */
add_action('init', function () {
    global $wp_post_types;

    $labels = [
        'name' => __('News', 'sage'),
        'singular_name' => __('News', 'sage'),
        'menu_name' => __('News', 'sage'),
        'all_items' => __('All News', 'sage'),
        'add_new_item' => __('Add New News', 'sage'),
        'edit_item' => __('Edit News', 'sage'),
        'new_item' => __('New News', 'sage'),
        'view_item' => __('View News', 'sage'),
        'search_items' => __('Search News', 'sage'),
        'not_found' => __('No News found', 'sage'),
        'not_found_in_trash' => __('No News found in Trash', 'sage'),
    ];

    foreach ($labels as $key => $value) {
        $wp_post_types['post']->labels->{$key} = $value;
    }

    $wp_post_types['post']->label = $labels['name'];
}, 100);

/**
 * Register custom post types.
 *
 * @return void
 */
add_action('init', function () {
    register_post_type('case-study', [
        'labels' => [
            'name' => __('Case Studies', 'sage'),
            'singular_name' => __('Case Study', 'sage'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-analytics',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'case-studies'],
    ]);

    register_post_type('testimonial', [
        'labels' => [
            'name' => __('Testimonials', 'sage'),
            'singular_name' => __('Testimonial', 'sage'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-testimonial',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'testimonials'],
    ]);

    register_post_type('cycle-route', [
        'labels' => [
            'name' => __('Cycle Routes', 'sage'),
            'singular_name' => __('Cycle Route', 'sage'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-location-alt',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'cycle-routes'],
    ]);
});
