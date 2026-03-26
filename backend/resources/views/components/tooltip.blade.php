@props([
    'title' => '',
    'position' => 'top',
])

<span class="relative inline-flex group">
    {{ $slot }}
    @if($title)
        <span class="pointer-events-none absolute z-50 whitespace-nowrap rounded-md bg-black/80 px-2 py-1 text-[10px] font-semibold text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100"
              @class([
                  '-top-8 left-1/2 -translate-x-1/2' => $position === 'top',
                  '-bottom-8 left-1/2 -translate-x-1/2' => $position === 'bottom',
                  'top-1/2 -translate-y-1/2 -left-2 translate-x-[-100%]' => $position === 'left',
                  'top-1/2 -translate-y-1/2 -right-2 translate-x-[100%]' => $position === 'right',
              ])>
            {{ $title }}
        </span>
    @endif
</span>

