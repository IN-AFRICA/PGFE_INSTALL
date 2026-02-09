<x-layouts.backend-layout :breadcrumbs="[['title' => 'Dashboard Infrastructure']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
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
                    <span class="text-xs font-black text-gray-400 tracking-widest uppercase">Signalements</span>
                </div>
                <div class="text-4xl font-black text-gray-800 dark:text-white">{{ $totalSignalements }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Recent Inventories -->
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-8 border-b border-gray-50 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="text-xl font-black text-gray-800 dark:text-white">Derniers Inventaires</h2>
                    <a href="{{ route('admin.infra-infrastructure-inventaires.index') }}" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline">Voir tout</a>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($recentInventaires as $inv)
                        <div class="p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                    <iconify-icon icon="lucide:file-text" width="20"></iconify-icon>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 dark:text-white">{{ $inv->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $inv->infrastructure->name }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full">{{ $inv->status }}</span>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-400 font-bold">Aucun inventaire récent.</div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-8 border-b border-gray-50 dark:border-gray-800 flex items-center justify-between">
                    <h2 class="text-xl font-black text-gray-800 dark:text-white">Derniers Signalements</h2>
                    <a href="{{ route('admin.infra-etats.index') }}" class="text-xs font-black text-rose-600 uppercase tracking-widest hover:underline">Voir tout</a>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($recentEtats as $etat)
                        <div class="p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 flex items-center justify-center">
                                    <iconify-icon icon="lucide:alert-circle" width="20"></iconify-icon>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 dark:text-white">{{ $etat->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $etat->infrastructure->name }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-bold">{{ $etat->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-400 font-bold">Aucun signalement récent.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
