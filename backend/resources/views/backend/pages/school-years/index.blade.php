<x-layouts.backend-layout :breadcrumbs="[['title' => 'Années Scolaires']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/20">
                        <iconify-icon icon="lucide:calendar-range" width="28"></iconify-icon>
                    </div>
                    Années Scolaires
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gestion des exercices académiques et bascule de l'année active.</p>
            </div>
            <a href="{{ route('admin.school-years.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-8 py-4 text-sm font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                OUVRIR UNE ANNÉE
            </a>
        </div>

        <!-- Years List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Exercice</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-64 text-center">État du Système</th>
                             <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($schoolYears as $year)
                            <tr class="group hover:bg-emerald-50/20 dark:hover:bg-emerald-900/10 transition-colors {{ $year->is_active ? 'bg-emerald-50/30 dark:bg-emerald-900/20' : '' }}">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl {{ $year->is_active ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }} flex items-center justify-center font-black text-lg transition-all">
                                            <iconify-icon icon="lucide:calendar-clock" width="24"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="font-black text-2xl text-gray-800 dark:text-white tracking-tight">{{ $year->name }}</span>
                                            @if($year->is_active)
                                                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider mt-1">Année Courante</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @if($year->is_active)
                                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 pointer-events-none">
                                            <span class="relative flex h-3 w-3">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                            </span>
                                            <span class="text-xs font-black uppercase tracking-widest">ACTIVE</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 text-xs font-black uppercase tracking-widest">
                                            ARCHIVÉE
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        @if(!$year->is_active)
                                            <a href="{{ route('admin.school-years.activate', $year->id) }}" 
                                               onclick="return confirm('Attention : Activer une nouvelle année scolaire basculera tout le système sur cet exercice. Continuer ?')"
                                               class="h-10 px-4 flex items-center gap-2 rounded-xl bg-violet-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-violet-700 hover:-translate-y-0.5 transition-all shadow-md shadow-violet-600/20">
                                                <iconify-icon icon="lucide:power" width="16"></iconify-icon>
                                                Activer
                                            </a>
                                        @endif
                                        
                                        <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 mx-2"></div>

                                        <a href="{{ route('admin.school-years.edit', $year) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="18"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.school-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Supprimer cette année scolaire ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <iconify-icon icon="lucide:trash-2" width="18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:calendar-off" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Système non initialisé (Aucune année).</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
