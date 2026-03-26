@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Activités Scolaires" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                    <iconify-icon icon="lucide:party-popper" width="24"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950">Activités Scolaires</h1>
                    <p class="text-xs text-zinc-500 font-medium">Planification des événements, sorties et célébrations de l'établissement.</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end px-4 border-r border-zinc-200">
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Événements</span>
                    <span class="text-xl font-black text-zinc-950 tracking-tighter">{{ number_format($stats['total']) }}</span>
                </div>
                <a href="{{ route('admin.activities.create') }}" 
                   class="h-10 px-4 rounded-md bg-zinc-900 text-[11px] font-bold text-white uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="lucide:calendar-plus" width="16"></iconify-icon>
                    Nouvelle activité
                </a>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <form method="GET" action="{{ route('admin.activities.index') }}" class="p-4 flex flex-wrap gap-4 items-end">
                @if(!session('selected_school_id'))
                    <div class="w-64">
                        <label class="text-[10px] font-bold text-zinc-400 uppercase mb-2 block px-1 text-center md:text-left">Établissement</label>
                        <select name="school_id" class="w-full h-10 rounded-md border border-zinc-200 bg-white text-xs font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 outline-none transition-all">
                            <option value="">Tous les établissements</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex-1 min-w-[250px] relative group">
                    <label class="text-[10px] font-bold text-zinc-400 uppercase mb-2 block px-1 text-center md:text-left">Recherche d'événement</label>
                    <div class="relative">
                        <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-zinc-900 transition-colors" width="16"></iconify-icon>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, lieu ou mot-clé..." 
                               class="w-full h-10 pl-10 pr-4 rounded-md border border-zinc-200 bg-white text-xs font-medium focus:ring-1 focus:ring-zinc-950 outline-none transition-all" />
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="h-10 px-6 rounded-md bg-zinc-100 text-zinc-900 text-xs font-bold hover:bg-zinc-200 transition-all">
                        Filtrer
                    </button>
                    @if(request()->hasAny(['school_id','q']))
                        <a href="{{ route('admin.activities.index') }}" class="h-10 w-10 flex items-center justify-center rounded-md border border-zinc-200 text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-all">
                            <iconify-icon icon="lucide:refresh-ccw" width="16"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Désignation de l'activité</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Informations Logistiques</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Affectation</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($activities as $a)
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-center justify-center h-12 w-12 rounded border border-zinc-200 bg-zinc-50 leading-none">
                                            <span class="text-[9px] font-bold text-zinc-400 uppercase">{{ optional($a->start_date)->format('M') }}</span>
                                            <span class="text-lg font-black text-zinc-900">{{ optional($a->start_date)->format('d') }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-zinc-900 tracking-tight">{{ $a->label }}</p>
                                            <span class="inline-flex px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-bold uppercase tracking-widest mt-1">
                                                {{ $a->type ?? 'Général' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2 text-xs font-bold text-zinc-700">
                                            <iconify-icon icon="lucide:map-pin" class="text-zinc-400" width="14"></iconify-icon>
                                            {{ $a->place ?? 'Local de l\'école' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-zinc-400 uppercase tracking-tighter">
                                            <iconify-icon icon="lucide:clock" class="text-zinc-300" width="12"></iconify-icon>
                                            {{ optional($a->start_date)->format('d/m/Y') }} @if($a->end_date) → {{ $a->end_date->format('d/m/Y') }} @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 rounded-full bg-zinc-300"></div>
                                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-tight">
                                            {{ $a->school->name ?? 'Toutes les écoles' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.activities.edit', $a) }}" class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                                            <iconify-icon icon="lucide:edit-3" width="14"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.activities.destroy', $a) }}" method="POST" onsubmit="return confirm('Supprimer cet événement ?')" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-8 w-8 flex items-center justify-center rounded text-zinc-300 hover:text-red-600 hover:bg-red-50 transition-all">
                                                <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center max-w-xs mx-auto text-center">
                                        <div class="h-16 w-16 rounded-full bg-zinc-50 flex items-center justify-center mb-4 border border-zinc-100 shadow-inner">
                                            <iconify-icon icon="lucide:party-popper" class="text-zinc-200" width="32"></iconify-icon>
                                        </div>
                                        <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-widest">Aucune activité</h3>
                                        <p class="text-xs text-zinc-400 mt-2 leading-relaxed">Le calendrier est actuellement vide. Commencez à planifier les événements de l'année.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($activities->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection