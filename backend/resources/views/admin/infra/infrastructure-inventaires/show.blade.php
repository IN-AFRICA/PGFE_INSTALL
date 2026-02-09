<x-layouts.backend-layout :breadcrumbs="[['title' => 'Inventaires Infrastructure', 'url' => route('admin.infra-infrastructure-inventaires.index')], ['title' => 'Détails']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <iconify-icon icon="lucide:clipboard-check" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">{{ $inventaire->title }}</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Inventaire du {{ $inventaire->inventory_date->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.infra-infrastructure-inventaires.edit', $inventaire->id) }}" class="h-12 px-6 flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 font-bold hover:bg-indigo-600 hover:text-white transition-all">
                    Modifier
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Main Content -->
            <div class="md:col-span-2 space-y-10">
                <div class="bg-white dark:bg-gray-900 p-10 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Observations détaillées</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                            {{ $inventaire->description ?? 'Aucune description détaillée.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-10">
                <div class="bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
                    <div class="space-y-6">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Statut</p>
                            @php
                                $statusConfig = [
                                    'excellent' => ['bg' => 'bg-emerald-100 text-emerald-600', 'label' => 'Excellent'],
                                    'bon' => ['bg' => 'bg-blue-100 text-blue-600', 'label' => 'Bon'],
                                    'moyen' => ['bg' => 'bg-amber-100 text-amber-600', 'label' => 'Moyen'],
                                    'mauvais' => ['bg' => 'bg-orange-100 text-orange-600', 'label' => 'Mauvais'],
                                    'critique' => ['bg' => 'bg-rose-100 text-rose-600', 'label' => 'Critique'],
                                ];
                                $config = $statusConfig[$inventaire->status] ?? $statusConfig['bon'];
                            @endphp
                            <span class="inline-flex px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $config['bg'] }}">
                                {{ $config['label'] }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Infrastructure</p>
                            <p class="font-black text-gray-800 dark:text-white">{{ $inventaire->infrastructure->name }}</p>
                            <p class="text-xs text-gray-400 font-bold uppercase">{{ $inventaire->infrastructure->code }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Enregistré par</p>
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-black text-gray-400">
                                    {{ mb_substr($inventaire->author->name ?? '?', 0, 1) }}
                                </div>
                                <span class="text-sm font-bold text-gray-600 dark:text-gray-400">{{ $inventaire->author->name ?? 'Inconnu' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
