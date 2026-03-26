@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Élèves', 'url' => route('admin.students.index')]
    ]" current="Registres de Présences" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tighter text-zinc-900">Registres de Présences</h1>
                <p class="text-sm text-zinc-500 font-medium italic mt-1">Historique complet des pointages par établissement et par classe.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button class="h-9 px-4 rounded-md border border-zinc-200 bg-white text-xs font-bold text-zinc-600 hover:bg-zinc-50 transition-all flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="lucide:download" width="16"></iconify-icon>
                    EXPORTER CSV
                </button>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            
            <div class="p-4 border-b border-zinc-100 bg-zinc-50/30">
                <div class="relative max-w-md group">
                    <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-zinc-900 transition-colors" width="16"></iconify-icon>
                    <input type="text" placeholder="Rechercher un élève ou un matricule..."
                        class="w-full h-10 pl-10 pr-4 rounded-md border border-zinc-200 bg-white text-sm placeholder:text-zinc-400 focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 transition-all outline-none">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Date</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Élève & Matricule</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Localisation</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($presences as $presence)
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-zinc-900">{{ optional($presence->created_at)->format('d/m/Y') }}</span>
                                        <span class="text-[10px] text-zinc-400 font-medium">{{ optional($presence->created_at)->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-zinc-900 leading-tight">
                                            {{ trim(($presence->student?->firstname ?? '').' '.($presence->student?->lastname ?? '').' '.($presence->student?->name ?? '')) ?: 'Élève #'.$presence->student_id }}
                                        </span>
                                        <span class="text-[11px] font-mono text-zinc-400 mt-0.5">
                                            {{ $presence->student?->matricule ?? 'SANS MATRICULE' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-black text-zinc-600 uppercase tracking-tighter flex items-center gap-1">
                                            <iconify-icon icon="lucide:layout-grid" class="text-zinc-300"></iconify-icon>
                                            {{ $presence->classroom->name ?? '—' }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400 font-medium italic">
                                            {{ $presence->school->name ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($presence->presence)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-100 text-[10px] font-bold text-emerald-700 uppercase tracking-widest">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Présent
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-50 border border-zinc-200 text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-300"></span>
                                            Absent
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="h-8 w-8 inline-flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                                        <iconify-icon icon="lucide:more-horizontal" width="18"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-12 w-12 rounded-full bg-zinc-50 flex items-center justify-center mb-3">
                                            <iconify-icon icon="lucide:clipboard-x" class="text-zinc-200" width="24"></iconify-icon>
                                        </div>
                                        <p class="text-sm font-bold text-zinc-900">Aucune donnée</p>
                                        <p class="text-xs text-zinc-400 mt-1">Aucune présence enregistrée pour cette période.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($presences->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $presences->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection