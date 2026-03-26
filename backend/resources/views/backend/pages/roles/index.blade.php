@extends('backend.layouts.app')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Habilitations" />

    <div class="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-500">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 pb-8">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-zinc-950 flex items-center justify-center text-white shadow-lg shadow-zinc-200">
                    <iconify-icon icon="lucide:shield-check" width="24"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tighter text-zinc-950">Contrôle d'Accès</h1>
                    <p class="text-xs text-zinc-500 font-medium tracking-tight">Gérer les permissions et les affectations par rôle.</p>
                </div>
            </div>

            <button onclick="document.getElementById('modal-add-role').classList.remove('hidden')" 
                    class="h-9 px-4 bg-white border border-zinc-200 text-zinc-900 rounded-md text-[11px] font-bold uppercase tracking-widest hover:bg-zinc-50 transition-all shadow-sm flex items-center gap-2">
                <iconify-icon icon="lucide:plus-circle" width="14"></iconify-icon>
                Nouveau Rôle
            </button>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-zinc-50/50 p-2 rounded-lg border border-zinc-200/60">
            <div class="flex items-center gap-2 px-2">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Périmètre :</span>
                
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="flex items-center gap-2 h-8 px-3 rounded border border-zinc-200 bg-white text-xs font-bold text-zinc-900 hover:border-zinc-400 transition-all shadow-sm">
                        <iconify-icon icon="lucide:layers" width="14" class="text-zinc-400"></iconify-icon>
                        {{ $selectedRole ? ucfirst($selectedRole->name) : 'Choisir un rôle...' }}
                        <iconify-icon icon="lucide:chevron-down" width="12" class="ml-1 text-zinc-400"></iconify-icon>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute left-0 mt-2 w-64 bg-white border border-zinc-200 rounded-md shadow-xl z-50 py-1 animate-in zoom-in-95 duration-100">
                        @foreach($roles as $role)
                            <a href="{{ route('admin.roles.index', ['role_id' => $role->id]) }}" 
                               class="flex items-center justify-between px-3 py-2 text-xs font-medium hover:bg-zinc-50 {{ ($selectedRole && $selectedRole->id == $role->id) ? 'bg-zinc-50 text-zinc-900' : 'text-zinc-500' }}">
                                <span class="capitalize">{{ $role->name }}</span>
                                @if($selectedRole && $selectedRole->id == $role->id)
                                    <iconify-icon icon="lucide:check" class="text-zinc-900" width="12"></iconify-icon>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($selectedRole)
                <div class="flex items-center gap-4 px-4 text-[10px] font-bold uppercase tracking-tighter text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        {{ $selectedRole->users_count }} Utilisateurs actifs
                    </div>
                    <div class="h-4 w-px bg-zinc-200"></div>
                    <div class="flex items-center gap-1.5 italic">
                        ID: {{ str_pad($selectedRole->id, 3, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if($selectedRole)
                <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-zinc-200 bg-white flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-widest">Membres assignés</h3>
                        
                        <form action="{{ route('admin.roles.assign') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">
                            <select name="user_id" class="h-9 w-64 rounded-md border border-zinc-200 bg-zinc-50 px-3 text-[11px] font-bold text-zinc-600 focus:ring-1 focus:ring-zinc-950 transition-all uppercase tracking-wider">
                                <option value="">Ajouter un agent...</option>
                                @foreach($academicPersonnels as $ap)
                                    @if($ap->user && !$ap->user->hasRole($selectedRole->name))
                                        <option value="{{ $ap->user_id }}">{{ $ap->name }} ({{ $ap->school->name ?? 'N/A' }})</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="h-9 px-4 bg-zinc-900 text-white rounded-md text-[10px] font-bold uppercase tracking-widest hover:bg-black transition-all shadow-sm">
                                Assigner
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-zinc-50/50 border-b border-zinc-200">
                                    <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Profil Utilisateur</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Établissement</th>
                                    <th class="px-6 py-4 text-[10px] font-bold text-zinc-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @forelse($users as $user)
                                    <tr class="group hover:bg-zinc-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded border border-zinc-200 bg-white flex items-center justify-center text-zinc-900 font-black text-[10px]">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-zinc-900 truncate">{{ $user->name }}</p>
                                                    <p class="text-[10px] text-zinc-400 font-medium lowercase">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-tight">
                                                {{ $user->school->name ?? 'Direction' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('admin.roles.revoke') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button type="submit" class="h-8 w-8 inline-flex items-center justify-center rounded text-zinc-300 hover:text-red-600 hover:bg-red-50 transition-all" onclick="return confirm('Révoquer cet accès ?')">
                                                    <iconify-icon icon="lucide:user-minus" width="16"></iconify-icon>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white border border-zinc-200 border-dashed rounded-xl py-32 text-center shadow-inner">
                    <div class="flex flex-col items-center max-w-xs mx-auto">
                        <div class="h-16 w-16 rounded bg-zinc-50 border border-zinc-100 flex items-center justify-center mb-6">
                            <iconify-icon icon="lucide:layers" class="text-zinc-200" width="32"></iconify-icon>
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 tracking-tight">Aucun rôle sélectionné</h3>
                        <p class="text-xs text-zinc-400 mt-2 italic leading-relaxed">Utilisez le sélecteur ci-dessus pour afficher et gérer les personnels par rôle.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection