@props([
    'breadcrumbs' => [],
])

<div class="mb-6 flex flex-col gap-3 sm:mb-8 sm:flex-row sm:items-center sm:justify-between">
    <nav class="flex flex-wrap items-center gap-x-1 gap-y-1 text-xs font-semibold text-zinc-500"
        aria-label="{{ __('Fil d\'Ariane') }}">
        <a href="{{ route('admin.dashboard') }}"
            class="rounded-md px-2 py-1 transition-colors hover:bg-zinc-100 hover:text-zinc-900">
            {{ __('Dashboard') }}
        </a>
        @foreach ($breadcrumbs as $crumb)
            <span class="px-0.5 text-zinc-300">/</span>
            @php
                $label = $crumb['label'] ?? $crumb['title'] ?? '';
                $url = $crumb['url'] ?? null;
            @endphp
            @if ($url && $url !== '#')
                <a href="{{ $url }}"
                    class="rounded-md px-2 py-1 transition-colors hover:bg-zinc-100 hover:text-zinc-900">{{ $label }}</a>
            @else
                <span
                    class="rounded-md bg-zinc-100 px-2 py-1 font-bold text-zinc-900">{{ $label }}</span>
            @endif
        @endforeach
    </nav>
    @isset($title_after)
        <div class="flex flex-wrap items-center gap-2">{{ $title_after }}</div>
    @endisset
</div>
