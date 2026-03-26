@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Infrastructure', 'url' => route('admin.infra-infrastructures.index')]]" current="Tableau de bord" />

    <div class="space-y-8 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                    <iconify-icon icon="lucide:building-2" width="24"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950 uppercase italic">Infrastructure & Patrimoine</h1>
                    <p class="text-xs text-zinc-500 font-medium">Suivi technique des bâtiments, des équipements et de la maintenance préventive.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="h-10 px-4 rounded-md border border-zinc-200 bg-white text-[10px] font-bold text-zinc-600 uppercase tracking-widest hover:bg-zinc-50 transition-all flex items-center gap-2">
                    <iconify-icon icon="lucide:file-text" width="14"></iconify-icon>
                    Rapport d'état
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="group bg-white p-6 rounded-xl border border-zinc-200 shadow-sm hover:border-zinc-900 transition-all duration-300">
                <div class="flex items-start justify-between mb-8">
                    <div class="h-10 w-10 rounded-lg bg-zinc-50 border border-zinc-100 text-zinc-400 flex items-center justify-center group-hover:bg-zinc-900 group-hover:text-white transition-all">
                        <iconify-icon icon="lucide:home" width="20"></iconify-icon>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Bâtiments</span>
                        <div class="h-1 w-8 bg-zinc-100 mt-1 group-hover:bg-zinc-900 transition-all"></div>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-zinc-950 tracking-tighter">{{ $totalInfrastructures }}</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase italic">Unités</span>
                </div>
            </div>

            <div class="group bg-white p-6 rounded-xl border border-zinc-200 shadow-sm hover:border-zinc-900 transition-all duration-300">
                <div class="flex items-start justify-between mb-8">
                    <div class="h-10 w-10 rounded-lg bg-zinc-50 border border-zinc-100 text-zinc-400 flex items-center justify-center group-hover:bg-zinc-900 group-hover:text-white transition-all">
                        <iconify-icon icon="lucide:monitor-smartphone" width="20"></iconify-icon>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Équipements</span>
                        <div class="h-1 w-8 bg-zinc-100 mt-1 group-hover:bg-zinc-900 transition-all"></div>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-zinc-950 tracking-tighter">{{ $totalEquipements }}</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase italic">Actifs</span>
                </div>
            </div>

            <div class="group bg-white p-6 rounded-xl border border-zinc-200 shadow-sm hover:border-zinc-900 transition-all duration-300">
                <div class="flex items-start justify-between mb-8">
                    <div class="h-10 w-10 rounded-lg bg-zinc-50 border border-zinc-100 text-zinc-400 flex items-center justify-center group-hover:bg-zinc-900 group-hover:text-white transition-all">
                        <iconify-icon icon="lucide:list-checks" width="20"></iconify-icon>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Inventaires</span>
                        <div class="h-1 w-8 bg-zinc-100 mt-1 group-hover:bg-zinc-900 transition-all"></div>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-zinc-950 tracking-tighter">{{ $totalInventaires }}</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase italic">Fiches</span>
                </div>
            </div>

            <div class="group bg-white p-6 rounded-xl border border-zinc-200 shadow-sm hover:border-zinc-900 transition-all duration-300">
                <div class="flex items-start justify-between mb-8">
                    <div class="h-10 w-10 rounded-lg {{ ($totalPendingMaintenance ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-zinc-50 text-zinc-400' }} border border-transparent flex items-center justify-center group-hover:bg-zinc-900 group-hover:text-white transition-all">
                        <iconify-icon icon="lucide:alert-circle" width="20"></iconify-icon>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Maintenance</span>
                        <div class="h-1 w-8 {{ ($totalPendingMaintenance ?? 0) > 0 ? 'bg-rose-500' : 'bg-zinc-100' }} mt-1 group-hover:bg-zinc-900 transition-all"></div>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black {{ ($totalPendingMaintenance ?? 0) > 0 ? 'text-rose-600' : 'text-zinc-950' }} tracking-tighter">{{ $totalPendingMaintenance ?? 0 }}</span>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase italic whitespace-nowrap">En attente</span>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
                 <h2 class="text-xs font-bold text-zinc-400 uppercase tracking-[0.2em] mb-4 underline decoration-zinc-100 underline-offset-8">Derniers ajouts</h2>
                 <div class="h-32 flex items-center justify-center border-2 border-dashed border-zinc-100 rounded-lg text-zinc-300 text-[10px] uppercase font-bold">
                     Zone de contenu (Tables, listes, etc.)
                 </div>
            </div>
            
            <div class="bg-zinc-900 rounded-xl p-6 text-white shadow-xl">
                <h2 class="text-xs font-bold text-zinc-400 uppercase tracking-[0.2em] mb-6">Résumé Global</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                        <span class="text-[10px] font-medium text-zinc-400 uppercase">État général</span>
                        <span class="text-xs font-bold text-emerald-400 italic text-right uppercase">Excellent</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                        <span class="text-[10px] font-medium text-zinc-400 uppercase">Dernier Inventaire</span>
                        <span class="text-xs font-bold text-zinc-200 uppercase tracking-tighter italic">{{ date('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection