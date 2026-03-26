@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Supervision des Flux" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                    <iconify-icon icon="lucide:activity" width="24" class="{{ session('status') ? '' : 'animate-pulse' }}"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950">Supervision des Flux</h1>
                    <p class="text-xs text-zinc-500 font-medium">Monitoring des synchronisations entre terminaux et hub central.</p>
                </div>
            </div>

            <div class="flex flex-col md:items-end gap-3">
                <div class="flex items-center gap-3">
                    @if(session('status'))
                        <div class="px-3 py-1 rounded border border-emerald-100 bg-emerald-50 text-[10px] font-bold text-emerald-700 uppercase tracking-widest">
                            {{ session('status') }}
                        </div>
                    @else
                        <div class="flex items-center gap-2 px-3 py-1 rounded border border-zinc-200 bg-zinc-50">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Canal Actif</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.sync.run') }}">
                        @csrf
                        <button type="submit" class="h-9 px-4 rounded-md bg-zinc-900 text-white text-[11px] font-bold uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2 shadow-sm">
                            <iconify-icon icon="lucide:refresh-ccw" width="14"></iconify-icon>
                            Synchroniser
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Source / Noeud</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Volume de données</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Dernier signal</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">État</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($schools as $school)
                            @php
                                $status = match($school['status']) {
                                    'ok' => ['color' => 'emerald', 'label' => 'Opérationnel'],
                                    'warning' => ['color' => 'amber', 'label' => 'Latence'],
                                    'danger' => ['color' => 'rose', 'label' => 'Critique'],
                                    default => ['color' => 'zinc', 'label' => 'Inconnu']
                                };
                            @endphp
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded border border-zinc-200 bg-zinc-50 flex items-center justify-center text-[10px] font-bold text-zinc-400">
                                            {{ mb_substr($school['name'], 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-zinc-900 tracking-tight">{{ $school['name'] }}</p>
                                            <p class="text-[10px] font-medium text-zinc-400 flex items-center gap-1 uppercase tracking-tighter">
                                                <iconify-icon icon="lucide:map-pin" width="10"></iconify-icon>
                                                {{ $school['city'] }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-xs font-bold text-zinc-900">{{ $school['students_count'] }}</span>
                                            <span class="text-[9px] font-bold text-zinc-400 uppercase">Élèves</span>
                                        </div>
                                        <div class="h-3 w-px bg-zinc-100"></div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-xs font-bold text-zinc-900">{{ $school['personals_count'] }}</span>
                                            <span class="text-[9px] font-bold text-zinc-400 uppercase">Agents</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-zinc-800">
                                            {{ $school['last_sync'] ? \Carbon\Carbon::parse($school['last_sync'])->diffForHumans() : 'Néant' }}
                                        </span>
                                        <span class="text-[10px] font-medium text-zinc-400 font-mono mt-0.5">
                                            {{ $school['last_sync'] ? \Carbon\Carbon::parse($school['last_sync'])->format('H:i:s') : '--:--' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded border border-{{ $status['color'] }}-100 bg-{{ $status['color'] }}-50/50 text-[10px] font-bold text-{{ $status['color'] }}-700 uppercase tracking-widest">
                                        <span class="h-1.5 w-1.5 rounded-full bg-{{ $status['color'] }}-500 {{ $school['status'] == 'ok' ? '' : 'animate-pulse' }}"></span>
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection