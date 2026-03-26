@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Utilisateurs & Accès" />

    <div class="space-y-6 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-zinc-200 pb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-10 w-10 rounded bg-zinc-900 flex items-center justify-center text-white shadow-sm">
                        <iconify-icon icon="lucide:shield-check" width="20"></iconify-icon>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tighter text-zinc-950">Centrale des Utilisateurs</h1>
                </div>
                <p class="text-sm text-zinc-500 font-medium italic">Gestion des habilitations, des accès multi-écoles et des journaux de sécurité.</p>
            </div>
            
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-zinc-900 px-6 text-[11px] font-bold text-zinc-50 uppercase tracking-widest hover:bg-black transition-all shadow-md shadow-zinc-200">
                <iconify-icon icon="lucide:user-plus" width="16"></iconify-icon>
                Créer un compte
            </a>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-zinc-100 bg-zinc-50/30">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Filtres de recherche</span>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="p-6 grid gap-6 md:grid-cols-12 items-end">
                <div class="md:col-span-5 relative group">
                    <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 block px-1">Identité ou Email</label>
                    <div class="relative">
                        <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 group-focus-within:text-zinc-900 transition-colors" width="16"></iconify-icon>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un agent..." 
                               class="w-full h-10 pl-10 pr-4 rounded-md border border-zinc-200 bg-white text-sm font-medium placeholder:text-zinc-300 focus:ring-1 focus:ring-zinc-950 transition-all outline-none" />
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 block px-1">Niveau d'Accès</label>
                    <select name="role" class="w-full h-10 rounded-md border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 transition-all outline-none">
                        <option value="">Tous les grades</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(request('role') === $r)>{{ strtoupper($r) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 block px-1">Affectation</label>
                    <select name="school_id" class="w-full h-10 rounded-md border border-zinc-200 bg-white px-3 text-xs font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 transition-all outline-none">
                        <option value="">National (Toutes écoles)</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" @selected(request('school_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1 flex gap-2">
                    <button type="submit" class="h-10 w-full rounded-md bg-zinc-100 text-zinc-900 hover:bg-zinc-200 transition-all flex items-center justify-center">
                        <iconify-icon icon="lucide:filter" width="18"></iconify-icon>
                    </button>
                    @if(request()->hasAny(['q','role','school_id']))
                        <a href="{{ route('admin.users.index') }}" class="h-10 w-10 flex shrink-0 items-center justify-center rounded-md border border-zinc-200 bg-white text-zinc-400 hover:text-zinc-950 transition-all">
                            <iconify-icon icon="lucide:refresh-ccw" width="16"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-zinc-50/50 border-b border-zinc-200">
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Profil Utilisateur</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Affectation École</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em]">Privilèges</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($users as $u)
                            <tr class="group hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded border border-zinc-200 bg-zinc-50 flex items-center justify-center text-zinc-900 font-black text-xs">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-zinc-900 tracking-tight group-hover:underline underline-offset-4 decoration-zinc-200 transition-all">{{ $u->name }}</p>
                                            <p class="text-[11px] text-zinc-400 font-medium truncate lowercase leading-none mt-1">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($u->school)
                                        <div class="flex flex-col">
                                            <span class="text-[11px] font-bold text-zinc-700 uppercase tracking-tighter">{{ $u->school->name }}</span>
                                            <span class="text-[10px] text-zinc-400 font-medium italic">Zone Territoriale</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-900 text-white text-[9px] font-bold uppercase tracking-widest">
                                            <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Global Admin
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($u->roles->pluck('name') as $role)
                                            <span class="px-2 py-0.5 rounded border border-zinc-200 bg-white text-zinc-600 text-[10px] font-bold uppercase tracking-tight">
                                                {{ $role }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center gap-1">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="h-8 w-8 flex items-center justify-center rounded text-zinc-400 hover:text-zinc-950 hover:bg-zinc-100 transition-all" title="Modifier">
                                            <iconify-icon icon="lucide:edit-3" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Supprimer cet accès ?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-8 w-8 flex items-center justify-center rounded text-zinc-300 hover:text-red-600 hover:bg-red-50 transition-all" title="Supprimer">
                                                <iconify-icon icon="lucide:trash-2" width="16"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <iconify-icon icon="lucide:ghost" class="text-zinc-100 mb-4" width="64"></iconify-icon>
                                        <h3 class="text-sm font-bold text-zinc-900">Aucun utilisateur trouvé</h3>
                                        <p class="text-xs text-zinc-400 mt-1 italic">Modifiez vos filtres de recherche ou créez un nouveau compte.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection