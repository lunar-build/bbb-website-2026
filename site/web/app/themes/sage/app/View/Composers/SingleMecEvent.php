<?php

namespace App\View\Composers;

use App\Support\Breadcrumbs;
use App\Support\ContentHero;
use App\Support\EventSessions;
use App\Support\PageMenu;
use Roots\Acorn\View\Composer;

use const App\CYCLING_CENTRE_EVENTS_CATEGORY;
use const App\CYCLING_CENTRE_EVENTS_PATH;

class SingleMecEvent extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single-mec-events',
    ];

    /**
     * Resolved eagerly, as in StickyNavTemplate: array values passed to
     * component attributes break when Acorn wraps them lazily.
     */
    protected function with()
    {
        return [
            'pageMenuItems' => PageMenu::forPost(),
            'hero' => ContentHero::forPost()['hero'],
            'body' => ContentHero::forPost()['body'],
            'breadcrumbs' => $this->breadcrumbs(),
            'nextSession' => $this->nextSession(),
            'priceLines' => $this->priceLines(),
            'venue' => $this->venue(),
        ];
    }

    /**
     * Cycling Centre sessions sit under What's on, matching their URL;
     * other events under the /events/ archive.
     */
    public function breadcrumbs(): array
    {
        $whatsOn = get_page_by_path(CYCLING_CENTRE_EVENTS_PATH);
        if (! $whatsOn || ! has_term(CYCLING_CENTRE_EVENTS_CATEGORY, 'mec_category')) {
            return [
                ['label' => __('Home', 'sage'), 'url' => home_url('/')],
                ['label' => __('Events', 'sage'), 'url' => get_post_type_archive_link('mec-events')],
                ['label' => html_entity_decode(get_the_title(), ENT_QUOTES)],
            ];
        }

        $trail = Breadcrumbs::forPost($whatsOn->ID);
        $trail[array_key_last($trail)]['url'] = get_permalink($whatsOn);
        $trail[] = ['label' => html_entity_decode(get_the_title(), ENT_QUOTES)];

        return $trail;
    }

    /**
     * "Wednesday 19th August 2026" / "9.45am", as on the Figma event page.
     * A finished one-off event shows when it ran, under "Date" instead.
     *
     * @return array{heading: string, date: string, time: string, finished: bool}|null
     */
    public function nextSession(): ?array
    {
        $shown = EventSessions::forDisplay(get_the_ID());
        if (! $shown) {
            return null;
        }
        $session = $shown['session'];
        $date = gmdate('l jS F Y', $session->tstart);
        // Multi-day events show their range.
        if (gmdate('Y-m-d', $session->tend) !== gmdate('Y-m-d', $session->tstart)) {
            $date .= ' – ' . gmdate('l jS F Y', $session->tend);
        }
        $allDay = get_post_meta(get_the_ID(), 'mec_allday', true) || get_post_meta(get_the_ID(), 'mec_hide_time', true);

        return [
            'heading' => $shown['upcoming'] ? __('Next session', 'sage') : __('Date', 'sage'),
            'date' => $date,
            'time' => $allDay ? __('All day', 'sage') : gmdate('g.ia', $session->tstart),
            'finished' => ! $shown['upcoming'],
        ];
    }

    /**
     * The event's MEC location: its name, address (when it adds anything to
     * the name) and a map search link. Null for MEC's "hide location" (1).
     *
     * @return array{name: string, address: string, map: string}|null
     */
    public function venue(): ?array
    {
        $term = wp_get_post_terms(get_the_ID(), 'mec_location')[0] ?? null;
        if (! $term || is_wp_error($term)) {
            return null;
        }
        $address = trim((string) get_term_meta($term->term_id, 'address', true));
        // Old one-off events stored the whole venue line as the address.
        if ($address === $term->name || str_starts_with($address, $term->name . ',')) {
            $address = ltrim(substr($address, strlen($term->name)), ', ');
        }

        return [
            'name' => html_entity_decode($term->name, ENT_QUOTES),
            'address' => $address,
            'map' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(trim($term->name . ', ' . $address, ', ')),
        ];
    }

    /**
     * MEC's cost field, one bullet per "|"-separated part (the format the
     * old site's price field used), shaped for <x-bullet-list>.
     */
    public function priceLines(): array
    {
        $cost = (string) get_post_meta(get_the_ID(), 'mec_cost', true);

        return array_map(
            fn($line) => ['text' => $line],
            array_values(array_filter(array_map('trim', explode('|', $cost)))),
        );
    }
}
