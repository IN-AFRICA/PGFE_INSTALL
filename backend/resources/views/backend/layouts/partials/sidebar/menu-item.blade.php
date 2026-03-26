@php
    /** @var \App\Services\MenuService\AdminMenuItem $item */
    $hasChildren = ! empty($item->children);
    $isActive = $item->active;
    $nested = $nested ?? false;
@endphp

@if($hasChildren)
    @php
        $menuService = app(\App\Services\MenuService\AdminMenuService::class);
        $childBranchActive = $menuService->shouldExpandSubmenu($item);
        $parentLooksActive = $isActive || $childBranchActive;
    @endphp
    <li class="block" x-data="{ open: {{ $childBranchActive ? 'true' : 'false' }} }">
        <div
            class="flex overflow-hidden rounded-xl ring-1 transition-colors duration-200 {{ $parentLooksActive ? 'bg-zinc-900/5 ring-zinc-200' : 'ring-transparent hover:ring-zinc-200/80' }}">
            <a href="{{ $item->route ?? '#' }}"
                @if($item->target) target="{{ $item->target }}" rel="noopener noreferrer" @endif
                class="admin-sidebar-link min-w-0 flex-1 !rounded-r-none {{ $parentLooksActive ? 'admin-sidebar-link-active' : 'admin-sidebar-link-idle' }}">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    @if($item->icon)
                        <iconify-icon icon="{{ $item->icon }}"
                            class="{{ $parentLooksActive ? 'text-zinc-300' : 'text-zinc-500' }} shrink-0"
                            width="18"></iconify-icon>
                    @endif
                    <span class="truncate">{{ $item->label }}</span>
                </div>
            </a>
            <button type="button" @click.prevent="open = !open"
                :aria-expanded="open"
                class="admin-sidebar-link flex shrink-0 items-center !rounded-l-none border-l px-2.5 !py-2.5 {{ $parentLooksActive ? 'admin-sidebar-link-active border-zinc-600/30' : 'admin-sidebar-link-idle border-zinc-200/60' }}">
                <span class="inline-flex transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                    <iconify-icon icon="lucide:chevron-down" class="text-current opacity-80" width="16"></iconify-icon>
                </span>
            </button>
        </div>
        <ul x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-0.5"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="ml-1 mt-1.5 space-y-0.5 border-l border-zinc-200/90 pl-3">
            @foreach ($item->children as $child)
                @include('backend.layouts.partials.sidebar.menu-item', ['item' => $child, 'nested' => true])
            @endforeach
        </ul>
    </li>
@else
    <li class="block">
        <a href="{{ $item->route ?? '#' }}"
            @if($item->target) target="{{ $item->target }}" rel="noopener noreferrer" @endif
            @class([
                'group',
                $nested ? 'admin-sidebar-sublink' : 'admin-sidebar-link',
                $nested
                    ? ($isActive ? 'admin-sidebar-sublink-active' : 'admin-sidebar-sublink-idle')
                    : ($isActive ? 'admin-sidebar-link-active' : 'admin-sidebar-link-idle'),
            ])>
            <div class="flex min-w-0 flex-1 items-center gap-3">
                @if($item->icon)
                    <iconify-icon icon="{{ $item->icon }}"
                        @class([
                            'shrink-0',
                            $isActive
                                ? ($nested ? 'text-zinc-800' : 'text-zinc-300')
                                : 'text-zinc-500 group-hover:text-zinc-700',
                        ])
                        width="{{ $nested ? '16' : '18' }}"></iconify-icon>
                @endif
                <span class="truncate">{!! $item->label !!}</span>
            </div>
            @if (! $nested)
                <iconify-icon icon="lucide:chevron-right"
                    class="shrink-0 text-zinc-400 opacity-0 transition-opacity group-hover:opacity-40"
                    width="14"></iconify-icon>
            @endif
        </a>
    </li>
@endif
