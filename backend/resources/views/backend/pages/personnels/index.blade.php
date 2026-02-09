<x-layouts.backend-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Personnel', 'url' => '#']]">
    <div class="space-y-6">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:id-card" class="text-emerald-500" width="32"></iconify-icon>
                    Gestion du Personnel
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administrez les agents et le personnel académique de vos établissements.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end mr-4">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Effectif RH</span>
                    <span class="text-xl font-black text-gray-800 dark:text-white">{{ number_format($stats['total']) }}</span>
                </div>
                <button disabled title="Création non implémentée" class="opacity-50 cursor-not-allowed inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all">
                    <iconify-icon icon="lucide:plus" width="20"></iconify-icon>
                    Nouveau Personnel
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.personnels.index') }}" class="grid gap-4 md:grid-cols-12 items-end">
                <div class="md:col-span-12 lg:col-span-8 flex flex-wrap gap-4">
                    @if(!session('selected_school_id'))
                        <div class="min-w-[250px] flex-1">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Établissement</label>
                            <select name="school_id" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500">
                                <option value="">Tous les établissements</option>
                                @foreach($schools as $sch)
                                    <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="h-11 px-8 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors">
                            Filtrer
                        </button>
                        @if(request()->has('school_id'))
                            <a href="{{ route('admin.personnels.index') }}" class="h-11 px-4 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-red-600 transition-colors" title="Vider les filtres">
                                <iconify-icon icon="lucide:refresh-cw" width="18"></iconify-icon>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Personnel Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Agent</th>
                            @if(!session('selected_school_id'))
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Affectation</th>
                            @endif
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">ID Système</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($personnels as $p)
                            @php
                                $dName = trim(($p->firstname ?? '').' '.($p->lastname ?? '').' '.($p->name ?? ''));
                                if($dName === '') $dName = 'Agent #'.$p->id;
                            @endphp
                            <tr class="group hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-black text-xs">
                                            {{ mb_substr($dName, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-white">{{ $dName }}</p>
                                            <p class="text-[10px] text-gray-400 font-medium">Cadre Académique</p>
                                        </div>
                                    </div>
                                </td>
                                @if(!session('selected_school_id'))
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase">
                                            <iconify-icon icon="mdi:school" class="text-gray-300"></iconify-icon>
                                            {{ $p->school->name ?? 'National' }}
                                        </div>
                                    </td>
                                @endif
                                <td class="px-6 py-4">
                                    <span class="text-xs font-mono font-bold text-gray-400 uppercase">USR_{{ str_pad((string)$p->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.personnels.edit', $p) }}" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-600">
                                            <iconify-icon icon="lucide:pen-line" width="18"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.personnels.destroy', $p) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-red-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-600">
                                                <iconify-icon icon="lucide:trash-2" width="18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl p-12">
                                        <iconify-icon icon="lucide:ghost" class="text-gray-200" width="80"></iconify-icon>
                                        <div class="text-center">
                                            <p class="text-gray-500 font-bold text-lg">Aucun agent trouvé</p>
                                            <p class="text-sm text-gray-400 mt-1">L'effectif semble vide pour les critères sélectionnés.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($personnels->hasPages())
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/30">
                    {{ $personnels->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
