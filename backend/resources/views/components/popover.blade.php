@props([
    'position' => 'top',
    'width' => 'w-64',
])

<div class="relative inline-flex" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @focusin="open = true" @focusout="open = false">
    <div @click="open = !open" class="cursor-pointer inline-flex items-center">
        {{ $trigger ?? $slot }}
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition
        class="absolute z-50 mt-2 rounded-md bg-white shadow-lg border border-gray-200 p-4 text-sm text-gray-700 {{ $width }}"
        @class([
            'left-1/2 -translate-x-1/2 -top-2 -translate-y-full' => $position === 'top',
            'left-1/2 -translate-x-1/2 top-full' => $position === 'bottom',
            '-left-2 translate-x-[-100%] top-1/2 -translate-y-1/2' => $position === 'left',
            '-right-2 translate-x-[100%] top-1/2 -translate-y-1/2' => $position === 'right',
        ])
    >
        {{ $slot }}
    </div>
</div>

