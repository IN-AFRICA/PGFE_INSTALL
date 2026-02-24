<x-layouts.modules-layout>
    <x-backend.pages.module-sidebar-layout 
        logoIcon="lucide:calendar-clock"
        :menuItems="[
            ['label' => 'Tableau de bord', 'icon' => 'lucide:layout-grid', 'url' => route('admin.planning.index'), 'route_prefix' => 'admin.planning.index'],
            ['label' => 'Saisie préalable', 'icon' => 'lucide:file-text', 'url' => '#', 'active' => false],
            ['label' => 'Saisie des opérations', 'icon' => 'lucide:clipboard-list', 'url' => route('admin.planning.index'), 'active' => true],
        ]"
    >
        <div class="space-y-6">
            <!-- Premium Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700">
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-2 italic">
                        <iconify-icon icon="lucide:calendar-clock" class="text-indigo-500" width="36"></iconify-icon>
                        Planification des Horaires
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Vue d'ensemble des devoirs et travaux planifiés par classe.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col items-end mr-4">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Planifications</span>
                        <span class="text-2xl font-black text-gray-800 dark:text-white">{{ number_format($stats['total']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
                <form method="GET" action="{{ route('admin.planning.index') }}" class="grid gap-4 md:grid-cols-12 items-end">
                    <div class="md:col-span-12 flex flex-wrap gap-4">
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
                        <div class="min-w-[200px]">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Classe</label>
                            <select name="classroom_id" class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500 focus:border-violet-500">
                                <option value="">Toutes</option>
                                @foreach($classrooms as $cl)
                                    <option value="{{ $cl->id }}" @selected(request('classroom_id') == $cl->id)>{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[250px]">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Recherche</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, mot-clé..." class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm px-3 focus:ring-violet-500 focus:border-violet-500" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="h-11 px-8 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-colors">
                                Filtrer
                            </button>
                            @if(request()->hasAny(['school_id','classroom_id','q']))
                                <a href="{{ route('admin.planning.index') }}" class="h-11 px-4 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-red-600 transition-colors" title="Vider les filtres">
                                    <iconify-icon icon="lucide:refresh-cw" width="18"></iconify-icon>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-xl font-black text-gray-800 dark:text-white">Liste des travaux</h2>
                    <a href="{{ route('admin.planning.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all hover:bg-violet-700 hover:scale-105 active:scale-95">
                        <iconify-icon icon="lucide:plus" width="20"></iconify-icon>
                        Nouvelle planification
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Titre</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Classe & Cours</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Établissement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($plannings as $p)
                                <tr class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-black text-xs">
                                                {{ mb_substr($p->label, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 dark:text-white">{{ $p->label }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Travail Dirigé</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1 text-xs font-bold text-gray-700">
                                                <iconify-icon icon="lucide:book-open" class="text-indigo-500"></iconify-icon>
                                                {{ $p->course->label ?? '-' }}
                                            </div>
                                            <div class="flex items-center gap-1 text-[10px] text-gray-400 font-bold uppercase">
                                                <iconify-icon icon="lucide:users" class="text-blue-500"></iconify-icon>
                                                {{ $p->classroom->name ?? '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">
                                        {{ $p->school->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-4 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-3xl p-12">
                                            <iconify-icon icon="lucide:ghost" class="text-gray-200" width="80"></iconify-icon>
                                            <div class="text-center">
                                                <p class="text-gray-500 font-bold text-lg">Aucune planification</p>
                                                <p class="text-sm text-gray-400 mt-1">Aucune planification trouvée pour les critères sélectionnés.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-backend.pages.module-sidebar-layout>
</x-layouts.modules-layout>
