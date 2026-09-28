{{-- Example: <x-filter-checkboxes :groups="[['heading' => 'Filter by location', 'items' => [...]], ...]" /> --}}
{{-- Figma "Filter checkboxes": desktop shows every group's checkboxes at
     all times; mobile collapses them behind a toggle button labelled with
     the current selected-filter count, expanding to the full grouped list
     on tap. This component is standalone/reusable for now — wiring it into
     an actual results/archive template (and re-running the filtering) is a
     separate, later piece of work; see resources/js/filter-checkboxes.js
     for the toggle + live count behaviour. --}}

@props([
    'groups' => [], // [['heading' => string, 'items' => [...]], ...] — see x-filter-checkbox-group for `items` shape
])

@php
    $selectedCount = collect($groups)->sum(fn($group) => collect($group['items'] ?? [])->where('checked', true)->count());
@endphp

@if (count($groups))
    <div {{ $attributes->class(['c-filter-checkboxes']) }}>
        <button type="button" class="c-filter-checkboxes__toggle" aria-expanded="false" aria-controls="{{ $attributes->get('id', 'filter-checkboxes') }}-panel">
            <span class="c-filter-checkboxes__toggle-label">{{ sprintf(_n('%d filter selected', '%d filters selected', $selectedCount, 'sage'), $selectedCount) }}</span>
            <x-icon name="chevron" />
        </button>

        <div class="c-filter-checkboxes__panel" id="{{ $attributes->get('id', 'filter-checkboxes') }}-panel" hidden>
            @foreach ($groups as $group)
                <x-filter-checkbox-group class="c-filter-checkboxes__group" :heading="$group['heading'] ?? null" :items="$group['items'] ?? []" />
            @endforeach
        </div>
    </div>
@endif
