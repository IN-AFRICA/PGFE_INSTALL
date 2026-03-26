@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Gestion des Inscriptions" />

    <div class="space-y-6 animate-in fade-in duration-500">

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3 flex-grow max-w-2xl">
                        <div class="relative flex-grow group">
                            <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-zinc-900 transition-colors" width="16"></iconify-icon>
                            <input type="text" placeholder="Rechercher un élève, matricule..."
                                class="w-full h-10 pl-10 pr-4 rounded-md border border-zinc-200 bg-zinc-50/50 text-xs font-medium focus:ring-1 focus:ring-zinc-950 outline-none transition-all">
                        </div>
                        <button class="inline-flex items-center gap-2 h-10 px-4 rounded-md border border-zinc-200 bg-white text-[10px] font-bold text-zinc-500 uppercase tracking-widest hover:bg-zinc-50 transition-all">
                            Filtres <iconify-icon icon="lucide:filter" width="14"></iconify-icon>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.registrations.create') }}"
                            class="inline-flex items-center gap-2 h-10 px-4 rounded-md bg-zinc-900 text-[10px] font-bold text-white hover:bg-black transition-all shadow-sm uppercase tracking-widest">
                            <iconify-icon icon="lucide:plus" width="14" />
                            Nouvel Élève
                        </a>
                        <button class="inline-flex items-center gap-2 h-10 px-4 rounded-md border border-zinc-200 bg-white text-[10px] font-bold text-zinc-500 uppercase tracking-widest hover:bg-zinc-50 transition-all">
                            Exporter <iconify-icon icon="lucide:download" width="14"></iconify-icon>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-6">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-zinc-50/50 border-y border-zinc-100">
                                <th class="px-6 py-3 w-10 text-center">
                                    <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                </th>
                                <th class="px-3 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-left">Matricule</th>
                                <th class="px-3 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-left">Identité</th>
                                <th class="px-3 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-left text-center">Sexe</th>
                                <th class="px-3 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-left">Classe / Section</th>
                                <th class="px-3 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-left">Naissance</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 text-[11px]">
                            @forelse($registrations as $r)
                                <tr class="hover:bg-zinc-50/50 transition-colors group">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="font-mono font-bold text-zinc-900 bg-zinc-100 px-1.5 py-0.5 rounded italic">{{ $r->student->matricule ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="flex flex-col leading-tight">
                                            <span class="font-bold text-zinc-900 uppercase tracking-tight">{{ $r->student->lastname }} {{ $r->student->name }}</span>
                                            <span class="text-zinc-400 text-[10px] font-medium">{{ $r->student->firstname }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full border border-zinc-200 text-zinc-500 font-bold uppercase text-[9px]">{{ $r->student->gender ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-zinc-800 tracking-tight">{{ $r->classroom->name ?? '—' }}</span>
                                            <span class="text-[10px] text-zinc-400 font-bold uppercase">{{ $r->student->section ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-zinc-500 font-medium">
                                        {{ $r->student->birth_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.registrations.edit', $r) }}"
                                                class="h-8 w-8 rounded-md text-zinc-400 flex items-center justify-center hover:bg-zinc-100 hover:text-zinc-900 transition-all">
                                                <iconify-icon icon="lucide:edit-3" width="14"></iconify-icon>
                                            </a>
                                            <form action="{{ route('admin.registrations.destroy', $r) }}" method="POST" onsubmit="return confirm('Supprimer cet élève ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="h-8 w-8 rounded-md text-zinc-300 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all">
                                                    <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center max-w-xs mx-auto">
                                            <iconify-icon icon="lucide:user-x" class="text-zinc-100 mb-4" width="48"></iconify-icon>
                                            <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-widest">Aucune inscription active</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 border-t border-zinc-100 pt-6">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection