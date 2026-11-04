<?php

require_once dirname(__DIR__, 2) . '/web/app/themes/sage/app/Support/HeadingLevel.php';

use App\Support\HeadingLevel;

test('a heading with text shows', function () {
    expect(HeadingLevel::shows(['text' => 'Hi', 'level' => 'h2']))->toBeTrue();
});

test('an empty or "not a heading" heading does not show', function () {
    expect(HeadingLevel::shows(['text' => '', 'level' => 'h2']))->toBeFalse()
        ->and(HeadingLevel::shows(['text' => 'Hi', 'level' => 'none']))->toBeFalse()
        ->and(HeadingLevel::shows(null))->toBeFalse();
});

test('headings inside a section go one level below its heading', function () {
    expect(HeadingLevel::inside(['text' => 'Hi', 'level' => 'h2']))->toBe('h3')
        ->and(HeadingLevel::inside(['text' => 'Hi', 'level' => 'h6']))->toBe('h6');
});

test('with no heading shown, inside headings take the section level', function () {
    expect(HeadingLevel::inside(['text' => '', 'level' => 'h2']))->toBe('h2')
        ->and(HeadingLevel::inside(['text' => '', 'level' => 'h3']))->toBe('h3')
        ->and(HeadingLevel::inside(null))->toBe('h2');
});
