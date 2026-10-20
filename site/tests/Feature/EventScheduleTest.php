<?php

/*
| App\EventTimetable\Schedule is plain PHP (no WordPress), so it's loaded
| directly, as PageMenuTest does for PageMenu.
*/

require_once dirname(__DIR__, 2) . '/web/app/themes/sage/app/EventTimetable/Schedule.php';

use App\EventTimetable\Schedule;

const LEARN_TO_RIDE = 753;
const FAMILY_CYCLING = 2083;

function springTimetable(array $overrides = []): array
{
    return array_merge([
        'start' => '2027-01-01',
        'end' => '2027-03-23',
        'category' => 26,
        'sessions' => [
            ['id' => 'sat-am', 'event' => LEARN_TO_RIDE, 'weekday' => 6, 'start' => '10:45', 'end' => '11:30'],
            ['id' => 'sat-pm', 'event' => LEARN_TO_RIDE, 'weekday' => 6, 'start' => '13:15', 'end' => '14:00'],
            ['id' => 'sun', 'event' => LEARN_TO_RIDE, 'weekday' => 7, 'start' => '09:45', 'end' => '10:30'],
            ['id' => 'tue', 'event' => LEARN_TO_RIDE, 'weekday' => 2, 'start' => '13:15', 'end' => '14:00'],
            ['id' => 'fam', 'event' => FAMILY_CYCLING, 'weekday' => 6, 'start' => '09:45', 'end' => '10:30'],
        ],
        'closures' => [],
        'cancellations' => [],
        'extras' => [],
    ], $overrides);
}

test('a 1 Jan – 23 Mar timetable expands to 48 Learn to Ride sessions', function () {
    $sessions = Schedule::expand(springTimetable());

    // 12 Saturdays × 2 + 12 Sundays + 12 Tuesdays.
    expect($sessions[LEARN_TO_RIDE])->toHaveCount(48)
        ->and($sessions[FAMILY_CYCLING])->toHaveCount(12)
        ->and($sessions[LEARN_TO_RIDE][0])->toBe(['date' => '2027-01-02', 'start' => '10:45', 'end' => '11:30'])
        ->and(end($sessions[LEARN_TO_RIDE]))->toBe(['date' => '2027-03-23', 'start' => '13:15', 'end' => '14:00']);
});

test('sessions come out in date then time order', function () {
    $keys = array_map(fn($s) => $s['date'] . $s['start'], Schedule::expand(springTimetable())[LEARN_TO_RIDE]);
    $sorted = $keys;
    sort($sorted);

    expect($keys)->toBe($sorted);
});

test('a closure removes every session in its date range', function () {
    $sessions = Schedule::expand(springTimetable([
        'closures' => [['id' => 'c1', 'from' => '2027-02-13', 'to' => '2027-02-21', 'label' => 'Half term']],
    ]));
    $dates = array_column($sessions[LEARN_TO_RIDE], 'date');

    // Two Saturdays (2 each), two Sundays, one Tuesday.
    expect($sessions[LEARN_TO_RIDE])->toHaveCount(48 - 7)
        ->and($dates)->not->toContain('2027-02-13')
        ->and($dates)->not->toContain('2027-02-21');
});

test('a cancellation removes one session on one date only', function () {
    $sessions = Schedule::expand(springTimetable([
        'cancellations' => [['id' => 'x1', 'date' => '2027-03-13', 'session' => 'sat-am']],
    ]))[LEARN_TO_RIDE];
    $onDay = array_values(array_filter($sessions, fn($s) => $s['date'] === '2027-03-13'));

    expect($sessions)->toHaveCount(47)
        ->and($onDay)->toBe([['date' => '2027-03-13', 'start' => '13:15', 'end' => '14:00']]);
});

test('extras are added, even on a closed date', function () {
    $sessions = Schedule::expand(springTimetable([
        'closures' => [['id' => 'c1', 'from' => '2027-02-17', 'to' => '2027-02-17', 'label' => '']],
        'extras' => [['id' => 'e1', 'date' => '2027-02-17', 'event' => LEARN_TO_RIDE, 'start' => '10:00', 'end' => '10:45']],
    ]))[LEARN_TO_RIDE];

    expect($sessions)->toHaveCount(49)
        ->and($sessions)->toContain(['date' => '2027-02-17', 'start' => '10:00', 'end' => '10:45']);
});

test('an extra matching a weekly session is not doubled up', function () {
    $sessions = Schedule::expand(springTimetable([
        'extras' => [['id' => 'e1', 'date' => '2027-01-02', 'event' => LEARN_TO_RIDE, 'start' => '10:45', 'end' => '11:30']],
    ]))[LEARN_TO_RIDE];

    expect($sessions)->toHaveCount(48);
});

test('incomplete or invalid sessions are skipped, not expanded', function () {
    $sessions = Schedule::expand(springTimetable(['sessions' => [
        ['id' => 'a', 'event' => 0, 'weekday' => 6, 'start' => '10:00', 'end' => '11:00'],
        ['id' => 'b', 'event' => LEARN_TO_RIDE, 'weekday' => 6, 'start' => '11:00', 'end' => '10:00'],
        ['id' => 'c', 'event' => LEARN_TO_RIDE, 'weekday' => 6, 'start' => '', 'end' => '10:00'],
    ]]));

    expect($sessions)->toBe([]);
});

test('a timetable without valid dates expands to nothing', function () {
    expect(Schedule::expand(springTimetable(['end' => '2026-12-01'])))->toBe([])
        ->and(Schedule::expand(springTimetable(['start' => ''])))->toBe([]);
});

test('a valid timetable has no errors', function () {
    expect(Schedule::validate(springTimetable())['errors'])->toBe([]);
});

test('validation flags bad dates, missing events, backwards times and same-event clashes', function () {
    $errors = Schedule::validate(springTimetable([
        'end' => '2026-12-01',
        'sessions' => [
            ['id' => 'no-event', 'event' => 0, 'weekday' => 1, 'start' => '10:00', 'end' => '11:00'],
            ['id' => 'backwards', 'event' => LEARN_TO_RIDE, 'weekday' => 1, 'start' => '12:00', 'end' => '11:00'],
            ['id' => 'one', 'event' => FAMILY_CYCLING, 'weekday' => 3, 'start' => '10:00', 'end' => '11:00'],
            ['id' => 'clash', 'event' => FAMILY_CYCLING, 'weekday' => 3, 'start' => '10:30', 'end' => '11:30'],
        ],
    ]))['errors'];

    expect(array_column($errors, 'ref'))->toContain('dates', 'no-event', 'backwards', 'clash');
});

test('different events at the same time are allowed', function () {
    $errors = Schedule::validate(springTimetable(['sessions' => [
        ['id' => 'a', 'event' => LEARN_TO_RIDE, 'weekday' => 6, 'start' => '10:00', 'end' => '11:00'],
        ['id' => 'b', 'event' => FAMILY_CYCLING, 'weekday' => 6, 'start' => '10:00', 'end' => '11:00'],
    ]]))['errors'];

    expect($errors)->toBe([]);
});

test('warnings: odd hours, closures outside the timetable, cancellations on the wrong day', function () {
    $warnings = Schedule::validate(springTimetable([
        'sessions' => [['id' => 'late', 'event' => LEARN_TO_RIDE, 'weekday' => 1, 'start' => '22:30', 'end' => '23:30']],
        'closures' => [['id' => 'away', 'from' => '2027-06-01', 'to' => '2027-06-02', 'label' => '']],
        'cancellations' => [['id' => 'wrong-day', 'date' => '2027-01-05', 'session' => 'late']],
    ]))['warnings'];

    expect(array_column($warnings, 'ref'))->toContain('late', 'away', 'wrong-day');
});
