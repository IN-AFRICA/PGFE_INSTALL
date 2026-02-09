<x-layouts.backend-layout :breadcrumbs="[['title' => 'Infrastructures']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-slate-600 flex items-center justify-center text-white shadow-lg shadow-slate-600/20">
                        <iconify-icon icon="lucide:building-2" width="28"></iconify-icon>
                    </div>
                    Patrimoine Immobilier
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Inventaire et gestion des infrastructures scolaires (Bâtiments, Salles, Terrains).</p>
            </div>
            <a href="{{ route('admin.infra-infrastructures.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-600 px-8 py-4 text-sm font-black text-white hover:bg-slate-700 shadow-xl shadow-slate-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVELLE INFRASTRUCTURE
            </a>
        </div>

        <!-- Filter Grid -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.infra-infrastructures.index') }}" class="grid gap-6 md:grid-cols-12 items-end">
                <div class="md:col-span-5 lg:col-span-6 relative">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Recherche</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <iconify-icon icon="lucide:search" width="20"></iconify-icon>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom du bâtiment, code..." class="w-full h-12 pl-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-slate-500 font-bold" />
                    </div>
                </div>
                 <div class="md:col-span-5 lg:col-span-4">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Type de bien</label>
                    <select name="type" class="w-full h-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm font-bold">
                        <option value="">Toutes catégories</option>
                        @foreach($types as $type)
                             <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="h-12 w-full rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white font-black hover:bg-gray-200 transition-all">
                        <iconify-icon icon="lucide:filter" width="20" class="mx-auto"></iconify-icon>
                    </button>
                     @if(request()->hasAny(['q','type']))
                        <a href="{{ route('admin.infra-infrastructures.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                            <iconify-icon icon="lucide:refresh-cw" width="20"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Infrastructure List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Identification</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Caractéristiques</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Financement</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($infrastructures as $infra)
                            <tr class="group hover:bg-slate-50/20 dark:hover:bg-slate-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center font-black text-lg">
                                            <iconify-icon icon="lucide:building" width="24"></iconify-icon>
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ $infra->name }}</p>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5">CODE: {{ $infra->code ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 text-xs font-black text-gray-600 dark:text-gray-300 w-fit">
                                            {{ $infra->categorie->name ?? 'Non classé' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                                            <iconify-icon icon="lucide:map-pin" width="10"></iconify-icon>
                                            {{ $infra->emplacement ?? 'Emplacement non défini' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-800 dark:text-white">
                                            {{ number_format($infra->montant_construction ?? 0, 0, ',', ' ') }} <span class="text-[10px] text-gray-400">USD</span>
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 mt-0.5">
                                            Bailleur: {{ $infra->bailleur->name ?? 'Fonds Propres' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.infra-infrastructures.edit', $infra->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-slate-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-infrastructures.destroy', $infra->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
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
                                        <iconify-icon icon="lucide:building-2" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucune infrastructure enregistrée.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($infrastructures->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $infrastructures->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>