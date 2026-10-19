<?php

namespace App\Blocks\Concerns;

/**
 * Field lookups with pattern-library fallback: the saved ACF value, else the
 * block's $example value when shows_example() allows it, else $default.
 */
trait ResolvesFields
{
    protected function resolveField(string $name, mixed $default = null): mixed
    {
        return get_field($name) ?: $this->exampleField($name, $default);
    }

    /**
     * True/false fields: a saved `false` is a real answer, so only fall back
     * when the field has never been saved (null).
     */
    protected function resolveBoolField(string $name, bool $default = false): bool
    {
        $value = get_field($name);

        return $value !== null ? (bool) $value : (bool) $this->exampleField($name, $default);
    }

    /**
     * The $example value on its own, for fallbacks that need extra
     * conditions or a different field name than the example key.
     */
    protected function exampleField(string $name, mixed $default = null): mixed
    {
        return shows_example($this->preview) ? ($this->example[$name] ?? $default) : $default;
    }
}
