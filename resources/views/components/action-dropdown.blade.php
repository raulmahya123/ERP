@props([
    'item',
    'label' => 'item ini',
    'routes' => [],
    'showEdit' => true,
    'showDelete' => true,
    'showDetail' => false,
])

@php
    $rEdit = $routes['edit'] ?? null;
    $rDelete = $routes['delete'] ?? null;
    $rDetail = $routes['show'] ?? null;
    $rCreate = $routes['create'] ?? null;
@endphp

<div x-data="{ open: false }" class="relative inline-block">
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white shadow hover:bg-emerald-700 ring-1 ring-emerald-700/20 transition"
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
        </svg>
        Actions
        <svg class="h-4 w-4 -mr-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        x-transition.origin.top.right
        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20"
    >
        @if ($showDetail && $rDetail && Route::has($rDetail))
            <a
                href="{{ route($rDetail, $item) }}"
                class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70"
            >
                <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 2c-2.21 0-4 1.343-4 3v1h8v-1c0-1.657-1.79-3-4-3z"/>
                </svg>
                Detail
            </a>
        @endif

        @if ($showEdit && $rEdit && Route::has($rEdit))
            <a
                href="{{ route($rEdit, $item) }}"
                class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70"
            >
                <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                </svg>
                Edit
            </a>
        @endif

        @if ($showDelete && $rDelete && Route::has($rDelete))
            <button
                type="button"
                data-action="{{ route($rDelete, $item) }}"
                data-label="{{ $label }}"
                @click="open = false; $dispatch('confirm-delete', { action: $el.dataset.action, label: $el.dataset.label })"
                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7v10m6-10v10M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M10 7V5a2 2 0 012-2h0a2 2 0 012 2v2"/>
                </svg>
                Delete
            </button>
        @endif
    </div>
</div>
