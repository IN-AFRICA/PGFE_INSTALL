@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Planification des Horaires" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                    <iconify-icon icon="lucide:calendar-clock" width="24"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950">Planification des Horaires</h1>
                    <p class="text-xs text-zinc-500 font-medium">Gestion et suivi des travaux planifiés par classe et établissement.</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end px-4 border-r border-zinc-200">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Total Planifié</span>
                    <span class="text-xl font-black text-zinc-950 tracking-tighter">{{ number_format($stats['total']) }}</span>
                </div>
                <a href="{{ route('admin.planning.create') }}" 
                   class="h-10 px-4 rounded-md bg-zinc-900 text-[11px] font-bold text-white uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="lucide:plus" width="16"></iconify-icon>
                    Nouvelle planification
                </a>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-3 bg-zinc-50/50 border-b border-zinc-100">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] px-2">Criblage & Recherche</span>
            </div>
            <form method="GET" action="{{ route('admin.planning.index') }}" class="p-6">
                <div class="flex flex-wrap gap-4 items-end">
                    @if(!session('selected_school_id'))
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-[10px] font-bold text-zinc-400 uppercase mb-2 block px-1">Établissement</label>
                            <select name="school_id" class="w-full h-10 rounded-md border border-zinc-200 bg-white text-xs font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 outline-none transition-all">
                                <option value="">Tous les établissements</option>
                                @foreach($schools as $sch)
                                    <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="w-48">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase mb-2 block px-1">Classe</label>
                        <select name="classroom_id" class="w-full h-10 rounded-md border border-zinc-200 bg-white text-xs font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 outline-none transition-all">
                            <option value="">Toutes les classes</option>
                            @foreach($classrooms as $cl)
                                <option value="{{ $cl->id }}" @selected(request('classroom_id') == $cl->id)>{{ $cl->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px] relative group">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase mb-2 block px-1">Recherche</label>
                        <div class="relative">
                            <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-zinc-900 transition-colors" width="16"></iconify-icon>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre du travail..." 
                                   class="w-full h-10 pl-10 pr-4 rounded-md border border-zinc-200 bg-white text-xs font-medium focus:ring-1 focus:ring-zinc-950 outline-none transition-all" />
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="h-10 px-6 rounded-md bg-zinc-100 text-zinc-900 text-xs font-bold hover:bg-zinc-200 transition-all">
                            Filtrer
                        </button>
                        @if(request()->hasAny(['school_id','classroom_id','q']))
                            <a href="{{ route('admin.planning.index') }}" class="h-10 w-10 flex items-center justify-center rounded-md border border-zinc-200 text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                <iconify-icon icon="lucide:refresh-ccw" width="16"></iconify-icon>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Désignation du travail</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Classe & Discipline</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Établissement</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($plannings as $p)
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 flex items-center justify-center rounded border border-zinc-200 bg-zinc-50 text-zinc-900 font-bold text-[10px]">
                                            {{ mb_substr($p->label, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-zinc-900 tracking-tight">{{ $p->label }}</p>
                                            <span class="inline-flex px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[9px] font-bold uppercase tracking-widest mt-0.5">Travail Dirigé</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-zinc-700">
                                            <iconify-icon icon="lucide:book-open" class="text-zinc-400" width="14"></iconify-icon>
                                            {{ $p->course->label ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-zinc-400 uppercase tracking-tighter">
                                            <iconify-icon icon="lucide:users" class="text-zinc-300" width="12"></iconify-icon>
                                            {{ $p->classroom->name ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-tight">
                                        {{ $p->school->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                                            <iconify-icon icon="lucide:eye" width="14"></iconify-icon>
                                        </button>
                                        <button class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                            <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center max-w-xs mx-auto">
                                        <div class="h-16 w-16 rounded-full bg-zinc-50 flex items-center justify-center mb-4 border border-zinc-100 shadow-inner">
                                            <iconify-icon icon="lucide:calendar-x" class="text-zinc-200" width="32"></iconify-icon>
                                        </div>
                                        <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-widest">Aucune planification</h3>
                                        <p class="text-xs text-zinc-400 mt-2 leading-relaxed">Ajustez vos filtres ou créez une nouvelle planification pour les élèves.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection