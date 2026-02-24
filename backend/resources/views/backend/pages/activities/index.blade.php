<x-layouts.modules-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Activités scolaires', 'url' => '#']]">
    <x-backend.pages.module-sidebar-layout 
        logoIcon="lucide:party-popper"
        :menuItems="[
            ['label' => 'Tableau de bord', 'icon' => 'lucide:layout-grid', 'url' => route('admin.activities.index'), 'route_prefix' => 'admin.activities.index'],
            ['label' => 'Saisie préalable', 'icon' => 'lucide:file-text', 'url' => '#', 'active' => false],
            ['label' => 'Saisie des opérations', 'icon' => 'lucide:clipboard-list', 'url' => route('admin.activities.index'), 'active' => true],
        ]"
    >
        <div class="space-y-6">
            <!-- Premium Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700">
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                        <iconify-icon icon="lucide:party-popper" class="text-emerald-500" width="36"></iconify-icon>
                        Activités Scolaires
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Planifiez et suivez les événements et activités de l'école.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col items-end mr-4">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Activités en cours</span>
                        <span class="text-2xl font-black text-gray-800 dark:text-white">{{ number_format($stats['total']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
                <form method="GET" action="{{ route('admin.activities.index') }}" class="grid gap-4 md:grid-cols-12 items-end">
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
                        <div class="flex-1 min-w-[250px]">
                            <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Recherche</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, lieu, description..." class="w-full h-11 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm px-3 focus:ring-violet-500 focus:border-violet-500" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="h-11 px-8 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors">
                                Filtrer
                            </button>
                            @if(request()->hasAny(['school_id','q']))
                                <a href="{{ route('admin.activities.index') }}" class="h-11 px-4 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-500 hover:text-red-600 transition-colors" title="Vider les filtres">
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
                    <h2 class="text-xl font-black text-gray-800 dark:text-white">Liste des activités</h2>
                    <a href="{{ route('admin.activities.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all hover:bg-violet-700 hover:scale-105 active:scale-95">
                        <iconify-icon icon="lucide:plus" width="20"></iconify-icon>
                        Nouvelle activité
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Activité</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Lieu & Période</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Établissement</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($activities as $a)
                                <tr class="group hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 font-black text-xs">
                                                {{ mb_substr($a->label, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 dark:text-white">{{ $a->label }}</p>
                                                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-tight">{{ $a->type ?? 'Général' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1 text-xs font-bold text-gray-600">
                                                <iconify-icon icon="lucide:map-pin" class="text-orange-500"></iconify-icon>
                                                {{ $a->place ?? 'Non spécifié' }}
                                            </div>
                                            <div class="flex items-center gap-1 text-[10px] text-gray-400 font-bold uppercase">
                                                <iconify-icon icon="lucide:calendar" class="text-blue-500"></iconify-icon>
                                                {{ optional($a->start_date)->format('d/m/Y') }} @if($a->end_date) - {{ $a->end_date->format('d/m/Y') }} @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase">
                                            <iconify-icon icon="mdi:school" class="text-gray-300"></iconify-icon>
                                            {{ $a->school->name ?? 'Tous' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <a href="#" class="p-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-violet-600 hover:text-white transition-all dark:bg-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-600">
                                                <iconify-icon icon="lucide:pen-line" width="18"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-4 rounded-3xl p-12">
                                            <iconify-icon icon="lucide:party-popper" class="text-gray-200" width="80"></iconify-icon>
                                            <div class="text-center">
                                                <p class="text-gray-500 font-bold text-lg">Aucune activité</p>
                                                <p class="text-sm text-gray-400 mt-1">Aucune activité n'a été planifiée pour le moment.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activities->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-900/30">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-backend.pages.module-sidebar-layout>
</x-layouts.modules-layout>
