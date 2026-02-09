<x-layouts.backend-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Élèves', 'url' => '#']]">
    <div class="space-y-6">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <iconify-icon icon="lucide:users" class="text-pink-500" width="32"></iconify-icon>
                    Annuaire des Élèves
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Consultez et gérez la base de données des élèves du réseau.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col items-end mr-4">
                    <span class="text-xs font-bold text-gray-400 uppercase">Total Effectif</span>
                    <span class="text-xl font-black text-gray-800 dark:text-white">{{ number_format($stats['total']) }}</span>
                </div>
                <button disabled class="opacity-50 cursor-not-allowed inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all">
                    <iconify-icon icon="lucide:user-plus" width="20"></iconify-icon>
                    Nouvel Élève
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.students.index') }}" class="grid gap-4 md:grid-cols-12 items-end">
                <div class="md:col-span-4 relative">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Recherche</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <iconify-icon icon="lucide:search" width="18"></iconify-icon>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, matricule..." class="w-full pl-10 h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500" />
                    </div>
                </div>

                @if(!session('selected_school_id'))
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Établissement</label>
                        <select name="school_id" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500">
                            <option value="">Toutes les écoles</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="h-11 px-6 rounded-xl bg-violet-600 text-white text-sm font-bold hover:bg-violet-700 transition-colors flex-1">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['q', 'school_id']))
                        <a href="{{ route('admin.students.index') }}" class="h-11 px-4 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-red-600 transition-colors" title="Réinitialiser">
                            <iconify-icon icon="lucide:refresh-cw" width="18"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Élève</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Matricule</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Genre</th>
                            @if(!session('selected_school_id'))
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">École</th>
                            @endif
                            <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($students as $st)
                            @php
                                $fullname = trim(($st->firstname ?? '').' '.($st->lastname ?? '').' '.($st->name ?? ''));
                                if($fullname === '') $fullname = 'Élève #'.$st->id;
                                $isFocused = request('focus') == $st->id;
                            @endphp
                            <tr class="group hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors {{ $isFocused ? 'bg-violet-50 dark:bg-violet-900/20' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 text-white font-black text-xs">
                                            {{ mb_substr($fullname, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-white">{{ $fullname }}</p>
                                            <p class="text-[10px] text-gray-400 uppercase font-black">ID: #{{ $st->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300">
                                        {{ $st->matricule ?? 'SANS MATRICULE' }}
                                    </code>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {{ $st->gender ?? 'N/A' }}
                                </td>
                                @if(!session('selected_school_id'))
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-tighter">{{ $st->school->name ?? '—' }}</span>
                                    </td>
                                @endif
                                <td class="px-6 py-4">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.students.edit', $st) }}" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300">
                                            <iconify-icon icon="lucide:eye" width="18"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $st) }}" method="POST" onsubmit="return confirm('Supprimer cet élève ?')" class="inline">
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
                                <td colspan="{{ session('selected_school_id') ? 4 : 5 }}" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center gap-2 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl p-10">
                                        <iconify-icon icon="lucide:users" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-500 font-bold">Aucun élève trouvé.</p>
                                        <p class="text-xs text-gray-400">Modifiez vos filtres ou effectuez une nouvelle recherche.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/30">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
