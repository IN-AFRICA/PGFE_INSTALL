@props([
    'logoIcon' => 'lucide:graduation-cap',
    'menuItems' => []
])

@php
    $currentRoute = request()->route()?->getName();
@endphp

<div class="flex flex-col lg:flex-row lg:items-start grow gap-8">
    {{-- Sidebar part --}}
    <aside class="w-full lg:w-64 flex-shrink-0 flex flex-col min-h-[calc(100vh-4rem)]">
        <!-- Logo Area -->
        <div class="mb-10 flex flex-col items-center lg:items-start pl-2">
            <div class="h-16 w-16 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center p-2 mb-2">
                <img src="/images/logo.png" alt="PGFE" class="h-12 w-12 object-contain" onerror="this.onerror=null; this.src='https://api.iconify.design/lucide:graduation-cap.svg';">
            </div>
            <span class="text-[10px] font-black tracking-[0.3em] text-[#0ea5e9] uppercase">PGFE</span>
        </div>

        <!-- Grouped Navigation -->
        <div class="space-y-6">
            <!-- Retour Button -->
            <div class="pl-2">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                    <iconify-icon icon="lucide:arrow-left" width="18" class="text-gray-400 group-hover:text-[#0ea5e9] transition-colors"></iconify-icon>
                    <span class="text-[11px] font-black text-gray-500 group-hover:text-[#0ea5e9] uppercase tracking-widest">Retour</span>
                </a>
            </div>

            <!-- Menu Card -->
            <nav class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-800 p-3 flex flex-col gap-1">
                @foreach ($menuItems as $item)
                    @php 
                        $active = $item['active'] ?? (isset($item['route_prefix']) && str_contains($currentRoute, $item['route_prefix']));
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="flex items-center gap-3 px-6 py-4 rounded-3xl text-[11px] font-black uppercase tracking-[0.1em] transition-all
                            {{ $active
                                ? 'bg-[#0ea5e9] text-white shadow-xl shadow-sky-500/30'
                                : 'text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-600' }}"
                    >
                        <iconify-icon icon="{{ $item['icon'] ?? 'lucide:circle' }}" width="20"></iconify-icon>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Déconnexion -->
        <div class="mt-auto pb-6 pl-2">
            <form method="POST" action="{{ route('web.auth.logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                    <iconify-icon icon="lucide:log-out" width="18" class="text-gray-400 group-hover:text-rose-600 transition-colors"></iconify-icon>
                    <span class="text-[11px] font-black text-gray-500 group-hover:text-rose-600 uppercase tracking-widest">Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="grow">
        {{ $slot }}
    </main>
</div>
