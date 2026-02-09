<x-layouts.backend-layout :breadcrumbs="[['title' => 'Inventaires Infrastructure']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <iconify-icon icon="lucide:clipboard-check" width="28"></iconify-icon>
                    </div>
                    Inventaires Infrastructure
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Suivi et état des lieux des bâtiments et infrastructures.</p>
            </div>
            <a href="{{ route('admin.infra-infrastructure-inventaires.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEL INVENTAIRE
            </a>
        </div>

        <!-- Inventaires List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">Date</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Titre / Infrastructure</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Statut</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Auteur</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($inventaires as $inventaire)
                            <tr class="group hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-gray-800 dark:text-white uppercase text-xs">{{ $inventaire->inventory_date->format('d M') }}</span>
                                        <span class="text-gray-400 text-xs">{{ $inventaire->inventory_date->format('Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-lg text-gray-800 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $inventaire->title }}</span>
                                        <span class="text-xs text-gray-400 font-bold uppercase tracking-wider flex items-center gap-1">
                                            <iconify-icon icon="lucide:building-2" width="12"></iconify-icon>
                                            {{ $inventaire->infrastructure->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
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
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-black text-gray-400">
                                            {{ mb_substr($inventaire->author->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-600 dark:text-gray-400">{{ $inventaire->author->name ?? 'Inconnu' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.infra-infrastructure-inventaires.edit', $inventaire->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-infrastructure-inventaires.destroy', $inventaire->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <iconify-icon icon="lucide:trash-2" width="20"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:clipboard-list" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun inventaire d'infrastructure enregistré.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($inventaires->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $inventaires->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
