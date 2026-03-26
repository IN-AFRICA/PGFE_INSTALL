@props([
    'links' => [], 
    'current' => '', 
])

<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 border-b border-zinc-200 pb-6">
    {{-- Breadcrumb Arrow Design --}}
    <nav class="flex items-center -space-x-3">
        {{-- Home / Root Item --}}
        <a href="{{ route('admin.dashboard') }}" 
           class="relative h-10 px-6 flex items-center bg-zinc-900 text-white font-bold text-[11px] uppercase tracking-widest transition-all hover:bg-black"
           style="clip-path: polygon(0% 0%, 90% 0%, 100% 50%, 90% 100%, 0% 100%);">
            <iconify-icon icon="lucide:home" class="mr-2"></iconify-icon>
            Home
        </a>

        {{-- Dynamic Links --}}
        @foreach($links as $link)
            <a href="{{ $link['url'] }}" 
               class="relative h-10 px-8 flex items-center bg-white border-y border-zinc-200 text-zinc-500 font-bold text-[11px] uppercase tracking-widest transition-all hover:bg-zinc-50 hover:text-zinc-900"
               style="clip-path: polygon(0% 0%, 90% 0%, 100% 50%, 90% 100%, 0% 100%, 10% 50%);">
                {{ $link['label'] }}
            </a>
        @endforeach

        {{-- Current Page (Active) --}}
        @if($current)
            <div class="relative h-10 px-8 flex items-center bg-zinc-100 border-y border-zinc-200 text-zinc-900 font-black text-[11px] uppercase tracking-widest"
                 style="clip-path: polygon(0% 0%, 95% 0%, 100% 50%, 95% 100%, 0% 100%, 10% 50%);">
                {{ $current }}
            </div>
        @endif
    </nav>

    {{-- Action Button --}}
    <a href="{{ route('admin.dashboard') }}"
       class="inline-flex h-10 items-center gap-2 px-5 rounded-md bg-white border border-zinc-200 text-[11px] font-black text-zinc-900 uppercase tracking-widest shadow-sm hover:bg-zinc-50 transition-all">
        <iconify-icon icon="lucide:layout-grid" class="text-lg"></iconify-icon>
        Dashboard
    </a>
</div>