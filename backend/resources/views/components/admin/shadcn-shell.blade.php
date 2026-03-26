@props([
    'title',
    'subtitle' => null,
    'icon' => 'lucide:layout-grid',
    'breadcrumbCurrent' => '',
    'breadcrumbExtras' => [],
    'module' => 'none',
])

@php
    $links = [['label' => 'Dashboard', 'url' => route('admin.dashboard')]];
    if ($module === 'infra') {
        $links[] = ['label' => 'Infrastructure', 'url' => route('admin.infra.dashboard')];
    } elseif ($module === 'stock') {
        $links[] = ['label' => 'Stock', 'url' => route('admin.stock-articles.index')];
    } elseif ($module === 'accounting') {
        $links[] = ['label' => 'Comptabilité', 'url' => route('admin.accounting.index')];
    }
    if (! empty($breadcrumbExtras)) {
        $links = array_merge($links, $breadcrumbExtras);
    }
@endphp

<div {{ $attributes->merge(['class' => 'space-y-6 animate-in fade-in duration-500']) }}>
    <x-breadcrumb :links="$links" :current="$breadcrumbCurrent" />

    <div class="flex flex-col justify-between gap-4 border-b border-zinc-200 pb-8 md:flex-row md:items-end">
        <div class="flex items-start gap-4">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-white shadow-sm">
                <iconify-icon icon="{{ $icon }}" width="20"></iconify-icon>
            </div>
            <div>
                <h1 class="text-3xl font-bold tracking-tighter text-zinc-900">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm font-medium italic text-zinc-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>

    {{ $slot }}
</div>
