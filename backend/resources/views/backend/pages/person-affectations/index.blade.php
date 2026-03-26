@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Gestion du personnel', 'url' => route('admin.personnels.index')]
    ]" current="Affectations" />

    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tighter text-zinc-900">Affectations du personnel</h1>
                <p class="text-sm text-zinc-500 font-medium italic mt-1">Lieux et missions assignés aux agents.</p>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Agent</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Lieu d'affectation</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Période</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Année scolaire</th>
                            @if(!session('selected_school_id'))
                                <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">École</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($affectations as $aff)
                            @php
                                $ap = $aff->academicPersonal;
                                $dName = trim(implode(' ', array_filter([$ap?->pre_name, $ap?->name, $ap?->post_name]))) ?: '—';
                            @endphp
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-zinc-900">{{ $dName }}</span>
                                    @if($ap?->matricule)
                                        <span class="block text-[11px] font-mono text-zinc-400 mt-0.5">{{ $ap->matricule }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-800">{{ $aff->lieu_affectation ?? '—' }}</td>
                                <td class="px-6 py-4 text-xs text-zinc-600">
                                    @if($aff->date_debut)
                                        {{ \Illuminate\Support\Carbon::parse($aff->date_debut)->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                    @if($aff->durree_jours)
                                        <span class="block text-[10px] text-zinc-400 mt-1">{{ $aff->durree_jours }} jour(s)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-zinc-600">{{ $aff->schoolYear->name ?? '—' }}</td>
                                @if(!session('selected_school_id'))
                                    <td class="px-6 py-4 text-xs text-zinc-600">{{ $aff->school->name ?? '—' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ session('selected_school_id') ? 4 : 5 }}" class="px-6 py-24 text-center text-sm text-zinc-500">
                                    Aucune affectation enregistrée pour les critères actuels.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($affectations->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $affectations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
