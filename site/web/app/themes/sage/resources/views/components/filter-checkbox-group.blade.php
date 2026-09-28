{{-- Example: <x-filter-checkbox-group heading="Filter by location" :items="[['label' => 'Bath', 'name' => 'location', 'value' => 'bath', 'checked' => true], ...]" /> --}}

@props([
    'heading' => null,
    'name' => null, // fallback `name` for any item that doesn't set its own
    'items' => [], // [['label' => string, 'name' => string|null, 'value' => string|null, 'checked' => bool], ...]
])

<fieldset {{ $attributes->class(['c-filter-checkbox-group']) }}>
    @if ($heading)
        <legend class="c-filter-checkbox-group__heading u-table-column-heading">{{ $heading }}</legend>
    @endif

    <div class="c-filter-checkbox-group__items">
        @foreach ($items as $item)
            <x-checkbox
                class="c-filter-checkbox-group__item"
                :label="$item['label']"
                :name="$item['name'] ?? $name"
                :value="$item['value'] ?? null"
                :checked="$item['checked'] ?? false"
            />
        @endforeach
    </div>
</fieldset>
