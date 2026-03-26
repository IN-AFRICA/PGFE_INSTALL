@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Années Scolaires" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                    <iconify-icon icon="lucide:calendar-range" width="24"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950">Années Scolaires</h1>
                    <p class="text-xs text-zinc-500 font-medium tracking-tight">Cycle de gestion des exercices académiques et bascule du système.</p>
                </div>
            </div>
            <a href="{{ route('admin.school-years.create') }}" 
               class="h-10 px-6 rounded-md bg-zinc-900 text-[11px] font-bold text-white hover:bg-black transition-all shadow-sm flex items-center gap-2 uppercase tracking-widest">
                <iconify-icon icon="lucide:calendar-plus" width="16"></iconify-icon>
                Ouvrir une année
            </a>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-100">
                            <th class="px-8 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Exercice Académique</th>
                            <th class="px-8 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-center">État du Système</th>
                             <th class="px-8 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Contrôle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($schoolYears as $year)
                            <tr class="group transition-colors {{ $year->is_active ? 'bg-emerald-50/10' : '' }}">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 flex items-center justify-center rounded border {{ $year->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : 'border-zinc-200 bg-zinc-50 text-zinc-400' }}">
                                            <iconify-icon icon="lucide:history" width="18"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="text-xl font-black text-zinc-900 tracking-tighter leading-none block">{{ $year->name }}</span>
                                            @if($year->is_active)
                                                <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest bg-emerald-100 px-1.5 py-0.5 rounded mt-1 inline-block">Session en cours</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @if($year->is_active)
                                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700">
                                            <span class="relative flex h-2 w-2">
                                              <span class="animate-ping absolute h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            <span class="text-[10px] font-bold uppercase tracking-widest">Active</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest border border-zinc-100 px-3 py-1 rounded-full bg-zinc-50">
                                            Archivée
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        @if(!$year->is_active)
                                            <a href="{{ route('admin.school-years.activate', $year->id) }}" 
                                               onclick="return confirm('Bascule du système vers {{ $year->name }} ?')"
                                               class="h-8 px-3 flex items-center gap-2 rounded bg-zinc-900 text-white font-bold text-[9px] uppercase tracking-widest hover:bg-black transition-all">
                                                <iconify-icon icon="lucide:power" width="14"></iconify-icon>
                                                Activer
                                            </a>
                                        @endif
                                        
                                        <div class="h-6 w-px bg-zinc-100 mx-1"></div>

                                        <a href="{{ route('admin.school-years.edit', $year) }}" class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                                            <iconify-icon icon="lucide:edit-3" width="14"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.school-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Action irréversible. Supprimer ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-8 w-8 flex items-center justify-center rounded text-zinc-300 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                                <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center max-w-xs mx-auto">
                                        <iconify-icon icon="lucide:calendar-off" class="text-zinc-100 mb-4" width="48"></iconify-icon>
                                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest leading-relaxed">Le système n'est pas initialisé.<br>Veuillez ouvrir une nouvelle année scolaire.</p>
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