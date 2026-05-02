<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    class="relative inline-flex"
>
    {{-- Tooltip trigger --}}
    <div @mouseenter="open = true" @mouseleave="open = false" @focus="open = true" @blur="open = false">
        {{ $trigger }}
    </div>

    {{-- Tooltip content --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 px-3 py-1.5 text-xs font-medium text-white bg-slate-800 dark:bg-slate-200 dark:text-slate-800 rounded-lg shadow-lg whitespace-nowrap {{ $position ?? 'bottom-full left-1/2 -translate-x-1/2 mb-1.5' }}"
        role="tooltip"
    >
        {{ $slot }}
        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px border-4 border-transparent border-t-slate-800 dark:border-t-slate-200"></div>
    </div>
</div>
