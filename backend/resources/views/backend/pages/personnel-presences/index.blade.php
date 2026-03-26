@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Gestion du personnel', 'url' => route('admin.personnels.index')]
    ]" current="Présences du personnel" />

    <div class="space-y-6 animate-in fade-in duration-500">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tighter text-zinc-900">Présences du personnel</h1>
                <p class="text-sm text-zinc-500 font-medium italic mt-1">Registre des pointages des agents par établissement.</p>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Date</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Agent</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Statut</th>
                            @if(!session('selected_school_id'))
                                <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">École</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($personPresences as $row)
                            @php
                                $p = $row->personnel;
                                $dName = trim(implode(' ', array_filter([$p?->pre_name, $p?->name, $p?->post_name]))) ?: '—';
                            @endphp
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-zinc-900">{{ optional($row->created_at)->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-zinc-900">{{ $dName }}</span>
                                    @if($p?->matricule)
                                        <span class="block text-[11px] font-mono text-zinc-400 mt-0.5">{{ $p->matricule }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($row->presence)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-100 text-[10px] font-bold text-emerald-700 uppercase tracking-widest">Présent</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-red-50 border border-red-100 text-[10px] font-bold text-red-700 uppercase tracking-widest">Absent</span>
                                    @endif
                                </td>
                                @if(!session('selected_school_id'))
                                    <td class="px-6 py-4 text-xs text-zinc-600">{{ $row->school->name ?? '—' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ session('selected_school_id') ? 3 : 4 }}" class="px-6 py-24 text-center text-sm text-zinc-500">
                                    Aucune présence enregistrée pour les critères actuels.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($personPresences->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $personPresences->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
