<x-layouts.backend-layout :breadcrumbs="[['title' => 'Gestion des Rôles']]">
    <div class="space-y-8">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <iconify-icon icon="lucide:shield-check" width="28"></iconify-icon>
                    </div>
                    Rôles & Autorisations
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gérez les privilèges d'accès et l'assignation du personnel académique.</p>
            </div>
            
            <button onclick="document.getElementById('modal-add-role').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEAU RÔLE
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Roles List -->
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-wider">Liste des Rôles</h3>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($roles as $role)
                            <a href="{{ route('admin.roles.index', ['role_id' => $role->id]) }}" 
                               class="flex items-center justify-between p-6 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-colors {{ ($selectedRole && $selectedRole->id == $role->id) ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-600' : '' }}">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                        <iconify-icon icon="lucide:users" width="20"></iconify-icon>
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-800 dark:text-white capitalize">{{ $role->name }}</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $role->users_count }} Utilisateurs</div>
                                    </div>
                                </div>
                                <iconify-icon icon="lucide:chevron-right" class="text-gray-300" width="20"></iconify-icon>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Role Assignment & Users -->
            <div class="xl:col-span-2 space-y-8">
                @if($selectedRole)
                    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black text-gray-800 dark:text-white">
                                    Assignation : <span class="text-indigo-600 capitalize">{{ $selectedRole->name }}</span>
                                </h3>
                                <p class="text-sm text-gray-400 font-medium mt-1">Gérez le personnel académique affecté à ce rôle.</p>
                            </div>
                            
                            <!-- Assign Form -->
                            <form action="{{ route('admin.roles.assign') }}" method="POST" class="flex items-center gap-3">
                                @csrf
                                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">
                                <select name="user_id" class="rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm font-bold h-12 px-4 focus:ring-indigo-600">
                                    <option value="">Sélectionner un membre...</option>
                                    @foreach($academicPersonnels as $ap)
                                        @if($ap->user && !$ap->user->hasRole($selectedRole->name))
                                            <option value="{{ $ap->user_id }}">{{ $ap->name }} {{ $ap->post_name }} ({{ $ap->school->name ?? 'N/A' }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="submit" class="h-12 px-6 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all">
                                    ASSIGNER
                                </button>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                                        <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Utilisateur</th>
                                        <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">ÉCOLE / FONCTION</th>
                                        <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($users as $user)
                                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 font-black">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-black text-gray-800 dark:text-white">{{ $user->name }}</div>
                                                        <div class="text-xs text-gray-400 font-medium">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-black text-gray-600 dark:text-gray-300 uppercase truncate max-w-[200px]">
                                                        {{ $user->school->name ?? 'SANS ÉCOLE' }}
                                                    </span>
                                                    @if($user->academicPersonal)
                                                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">{{ $user->academicPersonal->fonction->name ?? 'MEMBRE ACADÉMIQUE' }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <form action="{{ route('admin.roles.revoke') }}" method="POST" onsubmit="return confirm('Retirer ce rôle à cet utilisateur ?')">
                                                    @csrf
                                                    <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-rose-500 hover:bg-rose-500 hover:text-white transition-all">
                                                        <iconify-icon icon="lucide:user-minus" width="20"></iconify-icon>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center gap-4">
                                                    <iconify-icon icon="lucide:users" class="text-gray-200" width="64"></iconify-icon>
                                                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Aucun utilisateur assigné à ce rôle.</p>
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
                @else
                    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 p-20 text-center">
                        <div class="flex flex-col items-center gap-6 max-w-sm mx-auto">
                            <div class="h-24 w-24 rounded-[2rem] bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 shadow-inner">
                                <iconify-icon icon="lucide:shield" width="48"></iconify-icon>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-800 dark:text-white italic">Gestion des Accès</h3>
                                <p class="text-sm text-gray-400 font-medium mt-2 leading-relaxed">Sélectionnez un rôle dans la liste de gauche pour visualiser les membres ou effectuer de nouvelles assignations.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Add Role (Simple version) -->
    <div id="modal-add-role" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/80 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-xl font-black text-gray-800 dark:text-white">NOUVEAU RÔLE</h3>
                <button onclick="document.getElementById('modal-add-role').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <iconify-icon icon="lucide:x" width="24"></iconify-icon>
                </button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST" class="p-8 space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nom du Rôle</label>
                    <input type="text" name="name" required placeholder="ex: secretaire-ecole" 
                           class="w-full h-14 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none px-6 font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-600 transition-all">
                </div>
                <button type="submit" class="w-full h-14 rounded-2xl bg-indigo-600 text-white font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 transition-all">
                    CRÉER LE RÔLE
                </button>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>
