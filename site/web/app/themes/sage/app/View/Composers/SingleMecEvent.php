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
            'status' => $this->status(),
            'organizer' => $this->organizer(),
            'upcoming' => $this->upcoming(),
            'jsonLd' => $this->jsonLd(),
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
     * @return array{heading: string, date: string, time: string, note: string, finished: bool}|null
     */
    public function nextSession(): ?array
    {
        $shown = EventSessions::forDisplay(get_the_ID());
        if (! $shown) {
            return null;
        }
        $session = $shown['session'];
        $id = get_the_ID();
        $allDay = (bool) get_post_meta($id, 'mec_allday', true);
        $sameDay = gmdate('Y-m-d', $session->tend) === gmdate('Y-m-d', $session->tstart);
        $multiDay = ! $sameDay && ($allDay || $session->tend - $session->tstart >= DAY_IN_SECONDS);

        // MEC's "Hide Event Time" means no time line at all, not "All day".
        $time = get_post_meta($id, 'mec_hide_time', true) ? '' : ($allDay ? __('All day', 'sage') : gmdate('g.ia', $session->tstart));
        // End time too, unless hidden (an overnight 9pm–1am stays one date).
        if ($time && ! $allDay && ! $multiDay && $session->tend > $session->tstart && ! get_post_meta($id, 'mec_hide_end_time', true)) {
            $time .= ' – ' . gmdate('g.ia', $session->tend);
        }

        $date = gmdate('l jS F Y', $session->tstart);
        if ($multiDay) {
            // A range, the year only once when both ends share it.
            $sameYear = gmdate('Y', $session->tend) === gmdate('Y', $session->tstart);
            $date = gmdate($sameYear ? 'l jS F' : 'l jS F Y', $session->tstart) . ' – ' . gmdate('l jS F Y', $session->tend);
        }

        return [
            'heading' => $shown['upcoming'] ? __('Next session', 'sage') : __('Date', 'sage'),
            'date' => $date,
            'time' => $time,
            // MEC's "Time comment" under the date, e.g. "Doors open 15 minutes before".
            'note' => trim((string) (get_post_meta($id, 'mec_date', true)['comment'] ?? '')),
            'finished' => ! $shown['upcoming'],
        ];
    }

    /**
     * MEC's Event Status when it isn't going ahead as planned, as a message
     * (plus the cancellation reason when the editor ticked "display").
     */
    public function status(): ?string
    {
        $id = get_the_ID();
        $reason = trim((string) get_post_meta($id, 'mec_cancelled_reason', true));
        $showReason = $reason && get_post_meta($id, 'mec_display_cancellation_reason_in_single_page', true);

        return match (get_post_meta($id, 'mec_event_status', true)) {
            'EventCancelled' => __('This event has been cancelled.', 'sage') . ($showReason ? ' ' . $reason : ''),
            'EventPostponed' => __('This event has been postponed.', 'sage'),
            'EventRescheduled' => __('This event has been rescheduled.', 'sage'),
            default => null,
        };
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
     * MEC's Cost, one <x-bullet-list> item per "|"-separated part — the
     * format the editor's multi-line Cost box saves (app/filters.php).
     */
    public function priceLines(): array
    {
        $cost = trim((string) get_post_meta(get_the_ID(), 'mec_cost', true));

        // MEC's "This event is free" tick box saves the cost as 0.
        if ($cost === '0') {
            return [['text' => __('Free', 'sage')]];
        }

        return array_map(
            fn($line) => ['text' => is_numeric($line) ? '£' . $line : $line],
            // strlen, not a bare array_filter(), which would also drop a "0" part.
            array_values(array_filter(array_map('trim', explode('|', $cost)), 'strlen')),
        );
    }

    /**
     * The MEC organizer picked for the event, if any (MEC's default organizer
     * id 1 means none).
     *
     * @return array{name: string, tel: string, email: string, url: string}|null
     */
    public function organizer(): ?array
    {
        $term = get_term((int) get_post_meta(get_the_ID(), 'mec_organizer_id', true), 'mec_organizer');
        if (! $term || is_wp_error($term)) {
            return null;
        }
        $meta = fn(string $key) => trim((string) get_term_meta($term->term_id, $key, true));

        return [
            'name' => html_entity_decode($term->name, ENT_QUOTES),
            'tel' => $meta('tel'),
            'email' => $meta('email'),
            'url' => $meta('url'),
        ];
    }

    /**
     * Sessions after the one the info card shows, as "Wednesday 21st October,
     * 9.45am – 10.30am" lines. Only for events with more than one coming up.
     *
     * @return list<string>
     */
    public function upcoming(): array
    {
        $id = get_the_ID();
        if (get_post_meta($id, 'mec_hide_time', true)) {
            $time = fn($s) => '';
        } elseif (get_post_meta($id, 'mec_allday', true)) {
            $time = fn($s) => __('All day', 'sage');
        } else {
            $hideEnd = get_post_meta($id, 'mec_hide_end_time', true);
            $time = fn($s) => gmdate('g.ia', $s->tstart) . (! $hideEnd && $s->tend > $s->tstart ? ' – ' . gmdate('g.ia', $s->tend) : '');
        }

        return array_map(
            fn($s) => trim(gmdate('l jS F', $s->tstart) . ', ' . $time($s), ', '),
            array_slice(EventSessions::upcoming($id, 9), 1),
        );
    }

    /** schema.org Event data for search engines (MEC's own output isn't used on this template). */
    public function jsonLd(): ?string
    {
        $id = get_the_ID();
        $shown = EventSessions::forDisplay($id);
        if (! $shown) {
            return null;
        }
        $iso = fn($ts) => gmdate('Y-m-d\TH:i:s', $ts);
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => html_entity_decode(get_the_title(), ENT_QUOTES),
            'startDate' => $iso($shown['session']->tstart),
            'endDate' => $iso($shown['session']->tend),
            'eventStatus' => 'https://schema.org/' . (get_post_meta($id, 'mec_event_status', true) ?: 'EventScheduled'),
            'url' => get_permalink($id),
        ];
        if (has_post_thumbnail()) {
            $data['image'] = get_the_post_thumbnail_url(null, 'large');
        }
        if ($venue = $this->venue()) {
            $data['location'] = ['@type' => 'Place', 'name' => $venue['name'], 'address' => $venue['address'] ?: $venue['name']];
        }
        if ($organizer = $this->organizer()) {
            $data['organizer'] = array_filter(['@type' => 'Organization', 'name' => $organizer['name'], 'url' => $organizer['url'], 'email' => $organizer['email'], 'telephone' => $organizer['tel']]);
        }
        // Only a plain first price ("£12", "Free") becomes an offer; text prices stay on the page.
        $first = $this->priceLines()[0]['text'] ?? '';
        if (preg_match('/^£(\d+(?:\.\d+)?)$/', $first, $m) || $first === __('Free', 'sage')) {
            $data['offers'] = ['@type' => 'Offer', 'price' => $m[1] ?? '0', 'priceCurrency' => 'GBP', 'url' => get_permalink($id)];
        }

        return wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
