@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Gestion du Personnel" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-10 w-10 rounded bg-zinc-900 flex items-center justify-center text-white">
                        <iconify-icon icon="lucide:id-card" width="20"></iconify-icon>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tighter text-zinc-900">Gestion du Personnel</h1>
                </div>
                <p class="text-sm text-zinc-500 font-medium italic">Administration des agents et du personnel académique.</p>
            </div>
            
            <div class="flex items-center gap-6 bg-white border border-zinc-200 px-6 py-3 rounded-lg shadow-sm">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] leading-none mb-1">Effectif Total</p>
                    <p class="text-2xl font-black text-zinc-900 leading-none tracking-tighter">
                        {{ number_format($stats['total'] ?? 0) }}
                    </p>
                </div>
                <div class="h-8 w-px bg-zinc-100"></div>
                <button class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-900 px-6 text-xs font-bold text-white hover:bg-black transition-all shadow-lg shadow-zinc-200">
                    <iconify-icon icon="lucide:plus" width="16"></iconify-icon>
                    NOUVEL AGENT
                </button>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl p-2 shadow-sm">
            <form method="GET" action="{{ route('admin.personnels.index') }}" class="flex flex-wrap items-center gap-2">
                @if(!session('selected_school_id'))
                    <div class="relative flex-1 min-w-[300px]">
                        <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></iconify-icon>
                        <select name="school_id" class="w-full h-10 pl-10 pr-4 rounded-md border-transparent bg-zinc-50 text-xs font-bold text-zinc-600 focus:bg-white focus:ring-1 focus:ring-zinc-900 transition-all uppercase tracking-wider">
                            <option value="">Tous les établissements</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" @selected(request('school_id') == $sch->id)>{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <button type="submit" class="h-10 px-6 rounded-md bg-zinc-100 text-zinc-900 text-[11px] font-bold uppercase tracking-widest hover:bg-zinc-200 transition-colors">
                    Appliquer les filtres
                </button>
                
                @if(request()->filled('school_id'))
                    <a href="{{ route('admin.personnels.index') }}" class="h-10 w-10 flex items-center justify-center rounded-md border border-zinc-200 text-zinc-400 hover:text-red-600 hover:border-red-100 hover:bg-red-50 transition-all">
                        <iconify-icon icon="lucide:x" width="18"></iconify-icon>
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-zinc-50/50 border-b border-zinc-200">
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Agent & Profil</th>
                        @if(!session('selected_school_id'))
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Affectation</th>
                        @endif
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">ID Système</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($personnels as $p)
                        @php
                            $dName = trim(($p->pre_name ?? '').' '.($p->post_name ?? '').' '.($p->name ?? ''));
                            if($dName === '') $dName = 'Agent #'.$p->id;
                        @endphp
                        <tr class="group hover:bg-zinc-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 flex items-center justify-center rounded bg-zinc-100 border border-zinc-200 text-zinc-900 font-black text-xs">
                                        {{ mb_substr($dName, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-zinc-900 tracking-tight">{{ $dName }}</p>
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-0.5">Cadre Académique</p>
                                    </div>
                                </div>
                            </td>
                            @if(!session('selected_school_id'))
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-zinc-50 border border-zinc-200 text-[10px] font-bold text-zinc-500 uppercase tracking-tighter">
                                        <iconify-icon icon="lucide:school" width="12"></iconify-icon>
                                        {{ $p->school->name ?? 'National' }}
                                    </div>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <code class="text-[11px] font-bold text-zinc-400 bg-zinc-50 px-2 py-1 rounded">USR-{{ str_pad((string)$p->id, 5, '0', STR_PAD_LEFT) }}</code>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-1">
                                    <a href="{{ route('admin.personnels.edit', $p) }}" class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all" title="Modifier">
                                        <iconify-icon icon="lucide:edit-3" width="16"></iconify-icon>
                                    </a>
                                    <form action="{{ route('admin.personnels.destroy', $p) }}" method="POST" onsubmit="return confirm('Supprimer cet agent ?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Supprimer">
                                            <iconify-icon icon="lucide:trash-2" width="16"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-24">
                                <div class="flex flex-col items-center text-center">
                                    <div class="h-16 w-16 rounded-full bg-zinc-50 flex items-center justify-center mb-4">
                                        <iconify-icon icon="lucide:users-2" class="text-zinc-200" width="32"></iconify-icon>
                                    </div>
                                    <h3 class="text-sm font-bold text-zinc-900">Aucun agent trouvé</h3>
                                    <p class="text-xs text-zinc-400 mt-1 max-w-[250px]">L'effectif semble vide pour les critères sélectionnés actuellement.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($personnels->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $personnels->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection