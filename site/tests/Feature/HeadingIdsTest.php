<?php

/*
| App\Support\HeadingIds depends on PageMenu::slugify() only, so it runs without
| a WordPress bootstrap, loaded directly like PageMenuTest does.
*/

require_once dirname(__DIR__, 2) . '/web/app/themes/sage/app/Support/PageMenu.php';
require_once dirname(__DIR__, 2) . '/web/app/themes/sage/app/Support/HeadingIds.php';

use App\Support\HeadingIds;

beforeEach(fn() => HeadingIds::reset());

test('make slugs the heading text', function () {
    expect(HeadingIds::make('Report a Road Fault'))->toBe('report-a-road-fault');
});

test('make ignores markup and decodes entities', function () {
    expect(HeadingIds::make('<a href="/x">Cycling &amp; Walking</a>'))->toBe('cycling-walking');
});

test('repeated text gets -2, -3 and so on', function () {
    expect(HeadingIds::make('Learn more'))->toBe('learn-more')
        ->and(HeadingIds::make('Learn more'))->toBe('learn-more-2')
        ->and(HeadingIds::make('Learn more'))->toBe('learn-more-3');
});

test('text with nothing to slug gets no id', function () {
    expect(HeadingIds::make(''))->toBeNull()
        ->and(HeadingIds::make('<svg></svg>'))->toBeNull();
});

test('an id someone chose is never generated again', function () {
    HeadingIds::reserve('news');

    expect(HeadingIds::make('News'))->toBe('news-2');
});
