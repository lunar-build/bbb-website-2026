<?php

namespace App\Support;

class ListItems
{
    /**
     * Turn Bullet List's flat repeater rows ({text, level}) into a tree
     * ({text, children}) — each row nests under the nearest row above it
     * with a lower level. A level that jumps more than one deeper than the
     * row above is clamped, so a stray "2 levels" can't orphan an item.
     *
     * @param  array<int, array{text: string, level?: int|string}>  $items
     * @return array<int, array{text: string, children: array}>
     */
    public static function nest(array $items): array
    {
        $root = [];
        // $stack[n] = reference to the children array that level n items go into.
        $stack = [&$root];

        foreach ($items as $item) {
            $level = min((int) ($item['level'] ?? 0), count($stack) - 1);
            array_splice($stack, $level + 1);

            $stack[$level][] = ['text' => $item['text'] ?? '', 'children' => []];
            $stack[] = &$stack[$level][array_key_last($stack[$level])]['children'];
        }

        return $root;
    }
}
