<x-layouts.backend-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Comptes Utilisateurs', 'url' => '#']
]">
    <div class="space-y-6">
        <!-- Floating Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3 tracking-tighter">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                        <iconify-icon icon="lucide:shield-check" width="28"></iconify-icon>
                    </div>
                    Centrale des Utilisateurs
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gérez les accès, les rôles et les privilèges de sécurité du système.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-8 py-4 text-sm font-black text-white hover:bg-violet-700 shadow-xl shadow-violet-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                AJOUTER UN COMPTE
            </a>
        </div>

        <!-- Filters Grid -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-6 md:grid-cols-12 items-end">
                <div class="md:col-span-4 lg:col-span-5 relative">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Identité / Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <iconify-icon icon="lucide:search" width="20"></iconify-icon>
                        </span>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ex: Jean Dupont ou admin@ecole.com" class="w-full h-12 pl-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-violet-500" />
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Privilège (Rôle)</label>
                    <select name="role" class="w-full h-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm font-bold">
                        <option value="">Tous les grades</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(request('role') === $r)>{{ strtoupper($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Lieu d'Affectation</label>
                    <select name="school_id" class="w-full h-12 rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 text-sm font-bold">
                        <option value="">National (Toutes écoles)</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" @selected(request('school_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-1 flex gap-2">
                    <button type="submit" class="h-12 w-full rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white font-black hover:bg-gray-200 transition-all">
                        <iconify-icon icon="lucide:filter" width="20" class="mx-auto"></iconify-icon>
                    </button>
                    @if(request()->hasAny(['q','role','school_id']))
                        <a href="{{ route('admin.users.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all">
                            <iconify-icon icon="lucide:refresh-cw" width="20"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Utilisateur</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Affectation</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Niveau d'Accès</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($users as $u)
                            <tr class="group hover:bg-violet-50/20 dark:hover:bg-violet-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-violet-100 to-violet-200 dark:from-violet-900/40 dark:to-violet-800/40 text-violet-700 dark:text-violet-300 flex items-center justify-center font-black text-lg">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-white group-hover:text-violet-600 transition-colors">{{ $u->name }}</p>
                                            <p class="text-xs font-medium text-gray-400 mt-0.5">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($u->school)
                                        <div class="flex items-center gap-2">
                                            <iconify-icon icon="mdi:school" class="text-gray-300" width="16"></iconify-icon>
                                            <span class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $u->school->name }}</span>
                                        </div>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300 text-[10px] font-black uppercase tracking-widest">Administrateur Global</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($u->roles->pluck('name') as $role)
                                            <span class="px-3 py-1 rounded-xl bg-violet-600 text-white text-[10px] font-black uppercase tracking-tighter shadow-md shadow-violet-600/20">
                                                {{ $role }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-violet-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cet accès ?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:shield-off" class="text-gray-200" width="80"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun utilisateur correspondant aux critères.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-backend.pages.settings.layout></x-layouts.modules-layout>
