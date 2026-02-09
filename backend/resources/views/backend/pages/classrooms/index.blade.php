<x-layouts.backend-layout :breadcrumbs="[['title' => 'Classes']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <iconify-icon icon="lucide:layers" width="28"></iconify-icon>
                    </div>
                    Structure Pédagogique
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gestion des classes, des promotions et de l'organisation scolaire.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(session('selected_classroom_id'))
                    <form action="{{ route('admin.selections.classroom.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 px-6 py-4 text-sm font-black hover:bg-rose-600 hover:text-white transition-colors shadow-sm">
                            <iconify-icon icon="lucide:x-circle" width="18"></iconify-icon>
                            DÉSÉLECTIONNER
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.classrooms.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1">
                    <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                    NOUVELLE CLASSE
                </a>
            </div>
        </div>

        <!-- Filter Grid -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.classrooms.index') }}" class="grid gap-6 md:grid-cols-12 items-end">
                <div class="md:col-span-5 lg:col-span-6 relative">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Recherche</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <iconify-icon icon="lucide:search" width="20"></iconify-icon>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom de la classe (ex: 6ème A)..." class="w-full h-12 pl-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-indigo-500 font-bold" />
                    </div>
                </div>
                <div class="md:col-span-5 lg:col-span-4">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Établissement</label>
                    <select name="school_id" class="w-full h-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm font-bold">
                        <option value="">Tous les établissements</option>
                        @foreach(($schools ?? []) as $sch)
                            <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="h-12 w-full rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white font-black hover:bg-gray-200 transition-all">
                        <iconify-icon icon="lucide:filter" width="20" class="mx-auto"></iconify-icon>
                    </button>
                    @if(request()->hasAny(['q','school_id']))
                        <a href="{{ route('admin.classrooms.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                            <iconify-icon icon="lucide:refresh-cw" width="20"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Classrooms Table -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Désignation</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Filière / Option</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Rattachement</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse(($classrooms ?? []) as $c)
                            @php
                                $isSelected = session('selected_classroom_id') == $c->id;
                            @endphp
                            <tr class="group hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-colors {{ $isSelected ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : '' }}">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl {{ $isSelected ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }} flex items-center justify-center font-black text-sm shadow-sm transition-colors">
                                            {{ $c->id }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-white text-lg">{{ $c->name }}</p>
                                            @if($isSelected)
                                                <span class="inline-flex items-center gap-1 text-[10px] uppercase font-black tracking-widest text-indigo-600">
                                                    <iconify-icon icon="lucide:check-circle" width="10"></iconify-icon>
                                                    Active
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 text-xs font-bold text-gray-600 dark:text-gray-300">
                                        {{ $c->filiaire->name ?? 'Tronc Commun' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                                        <iconify-icon icon="mdi:school" class="text-gray-300"></iconify-icon>
                                        {{ $c->school->name ?? 'Non défini' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        @if(!$isSelected)
                                            <form action="{{ route('admin.selections.classroom.set') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="classroom_id" value="{{ $c->id }}">
                                                <button type="submit" class="h-10 px-4 rounded-xl bg-indigo-50 text-indigo-600 font-bold text-xs uppercase tracking-wider hover:bg-indigo-600 hover:text-white transition-colors">
                                                    Sélectionner
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('admin.classrooms.edit', $c) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="18"></iconify-icon>
                                        </a>
                                        
                                        <form action="{{ route('admin.classrooms.destroy', $c) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de cette classe ?')" class="inline">
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
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-3xl p-10">
                                        <iconify-icon icon="lucide:layers" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucune classe répertoriée.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($classrooms) && $classrooms->hasPages())
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950/50">
                    {{ $classrooms->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
