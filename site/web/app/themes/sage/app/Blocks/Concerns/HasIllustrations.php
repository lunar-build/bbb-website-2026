<?php

namespace App\Blocks\Concerns;

use App\Support\Illustrations;

/**
 * Block access to the shared illustration set (App\Support\Illustrations): the choices for an
 * illustration select and the URL for a saved value.
 */
trait HasIllustrations
{
    /**
     * Select choices for the given illustration keys (every one when none are given).
     *
     * @return array<string, string>
     */
    protected function illustrations(string ...$keys): array
    {
        return Illustrations::choices(...$keys);
    }

    /**
     * Field instructions for an illustration select that offers the whole set.
     */
    protected function illustrationInstructions(): string
    {
        return 'Any illustration can be chosen, but some suit a banner better than others: characters work best at the sides, and small objects (bottle, cone, lock, map, pedal) can look lost on their own.';
    }

    protected function illustrationUrl(?string $key): ?string
    {
        return Illustrations::url($key);
    }
}
