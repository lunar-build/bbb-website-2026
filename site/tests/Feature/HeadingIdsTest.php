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

test('addMissing gives every heading without an id one and leaves the rest alone', function () {
    $html = '<h1>Page title</h1><h2 id="mine" class="x">Mine</h2><h3 class="y">Learn more</h3><h3>Learn more</h3><h4><img src="a.png" alt=""></h4><h4><svg></svg></h4>';

    expect(HeadingIds::addMissing($html))->toBe(
        '<h1 id="page-title">Page title</h1><h2 id="mine" class="x">Mine</h2><h3 id="learn-more" class="y">Learn more</h3><h3 id="learn-more-2">Learn more</h3><h4 id="heading"><img src="a.png" alt=""></h4><h4 id="heading-2"><svg></svg></h4>',
    );
});

test('addMissing never repeats an id already on the page', function () {
    expect(HeadingIds::addMissing('<section id="news"></section><h2>News</h2>'))->toBe('<section id="news"></section><h2 id="news-2">News</h2>');
});

test('addMissing leaves html without headings untouched', function () {
    expect(HeadingIds::addMissing('<p>Hello</p>'))->toBe('<p>Hello</p>');
});
