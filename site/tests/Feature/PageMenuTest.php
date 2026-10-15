<?php

/*
|--------------------------------------------------------------------------
| PageMenu::slugify
|--------------------------------------------------------------------------
|
| App\Support\PageMenu (web/app/themes/sage/app/Support/PageMenu.php) lives
| in the Sage theme's own Composer project (separate "App\" PSR-4 autoload,
| separate vendor/) rather than this Bedrock root's, so it isn't reachable
| through this suite's normal autoloading — there's no existing bridge
| between the two in this repo. Loaded directly here instead, and only
| slugify() is exercised: it's the one piece of PageMenu with no WordPress
| function calls, so it runs without a WordPress bootstrap (this suite has
| none). forPost()/anchorFor() call parse_blocks()/wp_strip_all_tags() and
| aren't unit-testable here for the same reason.
|
*/

require_once dirname(__DIR__, 2).'/web/app/themes/sage/app/Support/PageMenu.php';

use App\Support\PageMenu;

test('slugify lowercases and hyphenates heading text', function () {
    expect(PageMenu::slugify('Report a Road Fault'))->toBe('report-a-road-fault');
});

test('slugify collapses non-alphanumeric runs into a single hyphen', function () {
    expect(PageMenu::slugify('Bath & North East Somerset'))->toBe('bath-north-east-somerset');
});

test('slugify trims leading/trailing hyphens', function () {
    expect(PageMenu::slugify('  Order a Map & Feedback!  '))->toBe('order-a-map-feedback');
});

test('slugify returns an empty string for text with no alphanumerics', function () {
    expect(PageMenu::slugify('—'))->toBe('');
});
