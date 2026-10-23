<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;

/**
 * The bundled illustrations (resources/images/illustrations, the old theme's artwork), the single
 * source of truth for every block that offers them. A fixed, developer-controlled set rather
 * than a media upload, so the front end always renders a bundled asset. Blocks pick the ones
 * they offer with choices(), and resolve a saved key with url().
 *
 * A key is what blocks save in post content, so never rename or remove one. 'cyclist' and
 * 'girl' are the same artwork under the two keys older blocks already saved.
 */
class Illustrations
{
    /**
     * @var array<string, array{0: string, 1: string}> key => [label, file]
     */
    public const CHARACTERS = [
        'instructor' => ['Cycle instructor', 'instructor.png'],
        'bmx' => ['BMX rider', 'bmx.png'],
        'cyclist' => ['Cyclist on bike', 'cyclist.png'],
        'girl' => ['Girl', 'cyclist.png'],
        'grandma' => ['Grandma', 'grandma.png'],
        'mountain_biker' => ['Mountain biker', 'mountain-biker.png'],
        'wheelchair_user' => ['Wheelchair user', 'wheelchair-user.png'],
        'young_girl' => ['Young girl', 'young-girl.png'],
    ];

    /**
     * @var array<string, array{0: string, 1: string}> key => [label, file]
     */
    public const OBJECTS = [
        'bottle' => ['Bottle', 'objects/bottle.png'],
        'cone' => ['Cone', 'objects/cone.png'],
        'helmet' => ['Cycling helmet', 'helmet.png'],
        'lights' => ['Lights', 'objects/lights.png'],
        'lock' => ['Lock', 'objects/lock.png'],
        'map' => ['Map', 'objects/map.png'],
        'pedal' => ['Pedal', 'objects/pedal.png'],
        'pump' => ['Bike pump', 'pump.png'],
    ];

    /**
     * Select choices (key => label) for the given keys, in the order given; every character, then
     * every object, when none are.
     *
     * @return array<string, string>
     */
    public static function choices(string ...$keys): array
    {
        $all = self::CHARACTERS + self::OBJECTS;
        $choices = [];

        foreach ($keys ?: array_keys($all) as $key) {
            if (isset($all[$key])) {
                $choices[$key] = $all[$key][0];
            }
        }

        return $choices;
    }

    /**
     * Select choices for every character, or every object.
     *
     * @return array<string, string>
     */
    public static function characters(): array
    {
        return array_map(fn($item) => $item[0], self::CHARACTERS);
    }

    /**
     * @return array<string, string>
     */
    public static function objects(): array
    {
        return array_map(fn($item) => $item[0], self::OBJECTS);
    }

    /**
     * The bundled asset URL for a saved key, or null when it's empty or unknown.
     */
    public static function url(?string $key): ?string
    {
        $file = (self::CHARACTERS + self::OBJECTS)[$key ?? ''][1] ?? null;

        return $file ? Vite::asset('resources/images/illustrations/' . $file) : null;
    }
}
