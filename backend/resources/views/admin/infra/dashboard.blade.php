@php
    $currentRoute = request()->route()?->getName();
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'lucide:layout-dashboard', 'url' => route('admin.infra.dashboard'), 'active' => $currentRoute === 'admin.infra.dashboard'],
        ['label' => 'Catégories', 'icon' => 'lucide:sidebar', 'url' => route('admin.infra-categories.index'), 'active' => str_contains($currentRoute, 'infra-categories')],
        ['label' => 'Bailleurs', 'icon' => 'lucide:users', 'url' => route('admin.infra-bailleurs.index'), 'active' => str_contains($currentRoute, 'infra-bailleurs')],
        ['label' => 'Équipements', 'icon' => 'lucide:monitor', 'url' => route('admin.infra-equipements.index'), 'active' => str_contains($currentRoute, 'infra-equipements')],
        ['label' => 'États', 'icon' => 'lucide:file-text', 'url' => route('admin.infra-etats.index'), 'active' => str_contains($currentRoute, 'infra-etats')],
        ['label' => 'Infrastructures', 'icon' => 'lucide:building-2', 'url' => route('admin.infra-infrastructures.index'), 'active' => str_contains($currentRoute, 'infra-infrastructures')],
        ['label' => 'Inventaires', 'icon' => 'lucide:clipboard-check', 'url' => route('admin.infra-inventaires.index'), 'active' => str_contains($currentRoute, 'infra-inventaires')],
    ];
@endphp

<x-layouts.modules-layout>
    <x-backend.pages.module-sidebar-layout logoIcon="lucide:building-2" :menuItems="$menuItems">
        <div class="space-y-10">
            <!-- Premium Header -->
            <div class="bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3 italic">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <iconify-icon icon="lucide:layout-dashboard" width="28"></iconify-icon>
                    </div>
                    Tableau de Bord Infrastructure
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Vue d'ensemble du patrimoine et de la maintenance.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white dark:bg-gray-900 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 group hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <iconify-icon icon="lucide:building-2" width="24"></iconify-icon>
                        </div>
                        <span class="text-xs font-black text-gray-400 tracking-widest uppercase">Bâtiments</span>
                    </div>
                    <div class="text-4xl font-black text-gray-800 dark:text-white">{{ $totalInfrastructures }}</div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 group hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-all">
                            <iconify-icon icon="lucide:monitor" width="24"></iconify-icon>
                        </div>
                        <span class="text-xs font-black text-gray-400 tracking-widest uppercase">Équipements</span>
                    </div>
                    <div class="text-4xl font-black text-gray-800 dark:text-white">{{ $totalEquipements }}</div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 group hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <iconify-icon icon="lucide:clipboard-check" width="24"></iconify-icon>
                        </div>
                        <span class="text-xs font-black text-gray-400 tracking-widest uppercase">Inventaires</span>
                    </div>
                    <div class="text-4xl font-black text-gray-800 dark:text-white">{{ $totalInventaires }}</div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-800 group hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-all">
                            <iconify-icon icon="lucide:alert-triangle" width="24"></iconify-icon>
                        </div>
                        <span class="text-xs font-black text-gray-400 tracking-widest uppercase">Maintenance</span>
                    </div>
                    <div class="text-4xl font-black text-gray-800 dark:text-white">{{ $totalPendingMaintenance ?? 0 }}</div>
                </div>
            </div>
        </div>
    </x-backend.pages.module-sidebar-layout>
</x-layouts.modules-layout>
