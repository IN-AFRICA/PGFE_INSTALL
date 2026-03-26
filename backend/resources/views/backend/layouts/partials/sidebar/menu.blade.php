@php
    $menuService = app(\App\Services\MenuService\AdminMenuService::class);
    $menuGroups = $menuService->getMenu();
    $user = auth()->user();
@endphp

@php
    $schools = \App\Models\School::orderBy('name')->get(['id', 'name']);
@endphp

<div class="admin-sidebar-panel flex h-full min-h-0 flex-col">
    <div class="border-b border-zinc-200/80 bg-white/60 px-4 py-4 backdrop-blur-sm">
        <div
            class="flex w-full items-center gap-3 rounded-xl border border-zinc-100 bg-white p-2.5 shadow-sm shadow-zinc-200/40">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-white shadow-md shadow-zinc-300/50">
                <iconify-icon icon="lucide:layout-dashboard" width="18"></iconify-icon>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[13px] font-bold leading-none tracking-tight text-zinc-900">{{ config('app.name') }}
                </p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Administration</p>
            </div>
        </div>
    </div>

    <div class="px-4 pb-2 pt-3">
        <div x-data="{ open: false, schoolSearch: '' }" class="relative">
            <button @click="open = !open" type="button"
                class="group flex w-full items-center justify-between rounded-xl border border-zinc-200/90 bg-white p-2.5 shadow-sm transition-all hover:border-zinc-300 hover:bg-zinc-50/80">
                <div class="flex min-w-0 items-center gap-3 overflow-hidden">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-xs font-bold text-white shadow-sm shadow-indigo-900/10">
                        {{ session('selected_school_name') ? mb_substr(session('selected_school_name'), 0, 2) : 'GL' }}
                    </div>
                    <div class="min-w-0 truncate text-left">
                        <p class="text-[10px] font-bold uppercase leading-none tracking-widest text-zinc-400">Contexte</p>
                        <p class="mt-1 truncate text-xs font-bold text-zinc-900">
                            {{ session('selected_school_name') ?? 'Vue globale' }}
                        </p>
                    </div>
                </div>
                <iconify-icon icon="lucide:chevrons-up-down"
                    class="shrink-0 text-zinc-400 transition-colors group-hover:text-zinc-700" width="14"></iconify-icon>
            </button>

            <div x-show="open" @click.away="open = false" x-cloak
                class="animate-in slide-in-from-top-2 absolute left-0 top-full z-[100] mt-2 w-72 rounded-xl border border-zinc-200 bg-white p-2 shadow-2xl shadow-zinc-900/10">
                <div class="mb-2 border-b border-zinc-100 px-2 pb-2">
                    <input x-model="schoolSearch" type="search" placeholder="Rechercher une école…" autocomplete="off"
                        class="w-full rounded-md border border-zinc-100 bg-zinc-50 p-2 text-xs text-zinc-800 placeholder:text-zinc-400 focus:border-zinc-300 focus:outline-none focus:ring-1 focus:ring-zinc-300">
                </div>
                <div class="custom-scrollbar max-h-60 overflow-y-auto">
                    <a href="{{ route('admin.school.switch', ['id' => 'all']) }}"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold text-zinc-600 hover:bg-zinc-50 {{ !session('selected_school_id') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                        <iconify-icon icon="lucide:globe" width="16"></iconify-icon>
                        Toutes les écoles
                    </a>
                    <div class="my-2 border-t border-zinc-50"></div>
                    @foreach ($schools as $sch)
                        <a href="{{ route('admin.school.switch', ['id' => $sch->id]) }}"
                            x-show="!schoolSearch || @js(mb_strtolower($sch->name, 'UTF-8')).includes(schoolSearch.toLowerCase())"
                            class="flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium text-zinc-700 hover:bg-zinc-100">
                            <span class="truncate">{{ $sch->name }}</span>
                            @if (session('selected_school_id') == $sch->id)
                                <iconify-icon icon="lucide:check" class="shrink-0 text-indigo-600" width="14"></iconify-icon>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <nav class="admin-sidebar-nav custom-scrollbar flex flex-1 flex-col overflow-y-auto">
        @foreach ($menuGroups as $groupName => $groupItems)
            <div>
                <h3 class="admin-sidebar-group-title">{{ __($groupName) }}</h3>
                <ul class="space-y-1">
                    @foreach ($groupItems as $item)
                        @include('backend.layouts.partials.sidebar.menu-item', ['item' => $item])
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="mt-auto border-t border-zinc-200/80 bg-white/50 p-4 backdrop-blur-sm">
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="group flex w-full items-center gap-3 rounded-xl border border-transparent p-2 transition-all hover:border-zinc-200 hover:bg-white hover:shadow-md focus:outline-none">
                <div class="relative shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f4f4f5&color=18181b&bold=true"
                        class="h-9 w-9 rounded-lg border border-zinc-200 object-cover" alt="">
                    <span
                        class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500"></span>
                </div>
                <div class="min-w-0 flex-1 text-left">
                    <p class="truncate text-xs font-bold leading-none text-zinc-900">{{ $user->name }}</p>
                    <p class="mt-1 truncate text-[10px] font-medium text-zinc-400">{{ $user->email }}</p>
                </div>
                <iconify-icon icon="lucide:chevrons-up-down"
                    class="shrink-0 text-zinc-400 transition-colors group-hover:text-zinc-800" width="14"></iconify-icon>
            </button>

            <div x-show="open" @click.away="open = false" x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="translate-y-2 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                class="absolute bottom-full left-0 z-50 mb-2 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white py-1.5 shadow-xl">
                <div class="mb-1 border-b border-zinc-50 px-3 py-2">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Compte</p>
                </div>
                <a href="#"
                    class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-50">
                    <iconify-icon icon="lucide:user-cog" width="16"></iconify-icon>
                    Profil
                </a>
                <form method="POST" action="{{ route('web.auth.logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-rose-600 transition-colors hover:bg-rose-50">
                        <iconify-icon icon="lucide:log-out" width="16"></iconify-icon>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
