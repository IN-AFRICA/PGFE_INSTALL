<x-layouts.backend-layout :breadcrumbs="[['title' => 'Équipements']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                        <iconify-icon icon="lucide:monitor-smartphone" width="28"></iconify-icon>
                    </div>
                    Équipements & Mobilier
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Inventaire du matériel, mobilier et équipements techniques.</p>
            </div>
            <a href="{{ route('admin.infra-equipements.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-8 py-4 text-sm font-black text-white hover:bg-blue-700 shadow-xl shadow-blue-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                AJOUTER UN ÉQUIPEMENT
            </a>
        </div>

        <!-- Filter Grid -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.infra-equipements.index') }}" class="grid gap-6 md:grid-cols-12 items-end">
                <div class="md:col-span-5 lg:col-span-6 relative">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Recherche</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <iconify-icon icon="lucide:search" width="20"></iconify-icon>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, numéro de série..." class="w-full h-12 pl-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-blue-500 font-bold" />
                    </div>
                </div>
                 <div class="md:col-span-5 lg:col-span-4">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Catégorie</label>
                    <select name="type" class="w-full h-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm font-bold">
                        <option value="">Toutes catégories</option>
                        <!-- Dynamic categories would go here -->
                        <option value="informatique">Informatique</option>
                        <option value="mobilier">Mobilier</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="h-12 w-full rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white font-black hover:bg-gray-200 transition-all">
                        <iconify-icon icon="lucide:filter" width="20" class="mx-auto"></iconify-icon>
                    </button>
                     @if(request()->hasAny(['q','type']))
                        <a href="{{ route('admin.infra-equipements.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                            <iconify-icon icon="lucide:refresh-cw" width="20"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Equipments List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Équipement</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Détails Techniques</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Acquisition</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($equipements as $item)
                            <tr class="group hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-lg">
                                            <iconify-icon icon="lucide:box" width="24"></iconify-icon>
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $item->name }}</p>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-0.5">CODE: {{ $item->code ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                         <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 text-xs font-black text-gray-600 dark:text-gray-300 w-fit">
                                            {{ $item->categorie->name ?? 'Non catégorisé' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                                            <iconify-icon icon="lucide:map-pin" width="10"></iconify-icon>
                                            {{ $item->emplacement ?? 'Non localisé' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-800 dark:text-white">
                                            {{ number_format($item->montant_acquisition ?? 0, 0, ',', ' ') }} <span class="text-[10px] text-gray-400">USD</span>
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 mt-0.5">
                                            {{ $item->date_acquisition ? \Carbon\Carbon::parse($item->date_acquisition)->format('d/m/Y') : 'Date inconnue' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.infra-equipements.edit', $item->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-equipements.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
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
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-3xl p-10">
                                        <iconify-icon icon="lucide:package-open" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun équipement répertorié.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($equipements->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $equipements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>