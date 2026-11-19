<?php

namespace App\CyclePlanner;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * What the Cycle Planner (cycleplanner.betterbybike.info, a separate React app) reads from
 * this site so its header matches: the menu and header settings, and a stylesheet of the
 * shared tokens and components (resources/styles/planner.scss).
 */
class CyclePlanner
{
    public const NS = 'bbb/v1';

    protected const STYLES_ROUTE = '/' . self::NS . '/planner/styles';

    /** Icons the planner header draws, as <x-icon> renders them. */
    protected const ICONS = ['logo', 'arrow-right', 'chevron', 'search', 'close', 'menu'];

    public static function boot(): void
    {
        add_action('rest_api_init', [static::class, 'routes']);
        add_filter('rest_pre_serve_request', [static::class, 'serveStyles'], 10, 3);
    }

    public static function routes(): void
    {
        $get = fn(callable $callback) => [
            'methods' => WP_REST_Server::READABLE,
            'callback' => $callback,
            'permission_callback' => '__return_true',
        ];

        // The live planner's endpoint and shape, kept so it keeps working until it moves to /planner/header.
        register_rest_route('bbb', '/menu', $get([static::class, 'legacyMenu']));
        register_rest_route(static::NS, '/planner/header', $get([static::class, 'header']));
        register_rest_route(static::NS, '/planner/styles', $get([static::class, 'styles']));
    }

    /** @return list<array{text: string, url: string, children: ?list<array>}> */
    public static function legacyMenu(): array
    {
        $map = function (array $items) use (&$map): ?array {
            return $items ? array_map(fn($item) => [
                'text' => $item['label'],
                'url' => $item['href'],
                'children' => $map($item['children']),
            ], $items) : null;
        };

        return $map(menu_items_to_array('primary_navigation')) ?? [];
    }

    public static function header(): array
    {
        $map = function (array $items) use (&$map): array {
            return array_map(fn($item) => [
                'label' => $item['label'],
                'href' => $item['href'],
                'hasLink' => nav_item_has_link($item),
                'children' => $map($item['children']),
            ], $items);
        };
        $link = fn($value) => ($value = normalize_link($value))['url'] ? $value : null;
        $cta = get_field('cta', 'option');
        $socials = social_links_from_options();
        $logo = get_field('logo_charcoal', 'option');

        return [
            'home' => home_url('/'),
            'siteName' => get_bloginfo('name'),
            'menuName' => wp_get_nav_menu_name('primary_navigation') ?: __('Primary', 'sage'),
            // An uploaded logo wins; otherwise the planner draws icons.logo, as the site does.
            'logo' => $logo['url'] ?? null,
            'menu' => $map(menu_items_to_array('primary_navigation')),
            'socials' => $socials,
            'cta' => $link($cta),
            'ctaMobile' => $link(get_field('cta_mobile', 'option') ?: $cta),
            'search' => ['action' => home_url('/'), 'name' => 's'],
            'icons' => array_combine(
                $names = array_merge(static::ICONS, array_column($socials, 'platform')),
                array_map(fn($name) => trim(Blade::render('<x-icon :name="$name" />', ['name' => $name])), $names),
            ),
        ];
    }

    /**
     * planner.scss's build, with the theme.json tokens it uses (WordPress only prints those on
     * its own pages). Font URLs in the build are root-relative, so they load from this site.
     */
    public static function styles(): WP_REST_Response
    {
        $tokens = wp_get_global_stylesheet(['variables']);
        $css = Vite::isRunningHot()
            ? sprintf('@import url("%s?direct");', Vite::asset('resources/styles/planner.scss')) . "\n" . $tokens
            : $tokens . "\n" . Vite::content('resources/styles/planner.scss');

        return new WP_REST_Response($css, 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /** Sends the stylesheet as CSS rather than a JSON-encoded string. */
    public static function serveStyles(bool $served, $result, WP_REST_Request $request): bool
    {
        if ($served || $request->get_route() !== static::STYLES_ROUTE || ! $result instanceof WP_REST_Response || $result->is_error()) {
            return $served;
        }

        echo $result->get_data();

        return true;
    }
}
