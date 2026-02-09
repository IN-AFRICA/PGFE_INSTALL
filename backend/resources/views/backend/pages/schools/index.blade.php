<x-layouts.backend-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Écoles', 'url' => '#']]">
    <div class="space-y-6">
        <!-- Modern Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="mdi:school" class="text-violet-600" width="32"></iconify-icon>
                    Gestion des Écoles
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gérez le réseau d'établissements scolaires et leurs localisations.</p>
            </div>
            <a href="{{ route('admin.schools.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-bold text-white hover:bg-violet-700 shadow-lg shadow-violet-600/20 transition-all hover:-translate-y-0.5">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                Ajouter une École
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Établissements</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-2xl font-black text-gray-800 dark:text-white">{{ $stats['total'] }}</span>
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-900/20">
                        <iconify-icon icon="lucide:building-2" width="20"></iconify-icon>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Provinces Couvertes</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-2xl font-black text-gray-800 dark:text-white">{{ $stats['provinces'] }}</span>
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/20">
                        <iconify-icon icon="lucide:map-pin" width="20"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.schools.index') }}" class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[300px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <iconify-icon icon="lucide:search" width="18"></iconify-icon>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher par nom, ville..." class="w-full pl-10 h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500" />
                </div>
                <button type="submit" class="h-11 px-6 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-200 transition-colors">
                    Filtrer
                </button>
                @if(request()->has('q'))
                    <a href="{{ route('admin.schools.index') }}" class="text-sm text-red-600 font-bold hover:underline">Réinitialiser</a>
                @endif
            </form>
        </div>

        <!-- Schools Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Établissement</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Type</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Localisation</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($schools as $s)
                            <tr class="group hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 font-black text-xs">
                                            @if($s->logo)
                                                <img src="{{ Storage::url($s->logo) }}" class="h-full w-full object-cover rounded-xl" />
                                            @else
                                                {{ mb_substr($s->name, 0, 2) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-white">{{ $s->name }}</p>
                                            <p class="text-xs text-gray-400">ID: #{{ str_pad((string)$s->id, 4, '0', STR_PAD_LEFT) }}</p>
                                            @if(session('selected_school_id') == $s->id)
                                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider">
                                                    <iconify-icon icon="lucide:check-circle" width="10"></iconify-icon>
                                                    École Active
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                        {{ $s->type->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $s->city }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $s->province->name ?? 'N/A' }}, {{ $s->country->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end items-center gap-2">
                                        @if(session('selected_school_id') != $s->id)
                                            <a href="{{ route('admin.dashboard', ['school_id' => $s->id]) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-all text-xs font-bold dark:bg-emerald-900/30 dark:text-emerald-300"
                                               title="Sélectionner cette école">
                                                <iconify-icon icon="lucide:check-square" width="16"></iconify-icon>
                                                Sélectionner
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold cursor-default">
                                                <iconify-icon icon="lucide:check-circle-2" width="16"></iconify-icon>
                                                Sélectionnée
                                            </span>
                                        @endif
                                        
                                        <a href="{{ route('admin.schools.edit', $s) }}" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300">
                                            <iconify-icon icon="lucide:edit-3" width="18"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.schools.destroy', $s) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-red-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300">
                                                <iconify-icon icon="lucide:trash-2" width="18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <iconify-icon icon="lucide:search-x" class="text-gray-300" width="48"></iconify-icon>
                                        <p class="text-gray-500 font-bold">Aucune école trouvée.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schools->hasPages())
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/30">
                    {{ $schools->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
