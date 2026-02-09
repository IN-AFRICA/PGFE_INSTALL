<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <iconify-icon icon="lucide:user-cog" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white">Modifier le Compte</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">Mise à jour des accès pour <span class="text-indigo-600">{{ $user->name }}</span>.</p>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="h-12 px-6 flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-black text-xs uppercase tracking-widest">
                <iconify-icon icon="lucide:arrow-left" width="18"></iconify-icon>
                RETOUR
            </a>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nom complet</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:user" width="18"></iconify-icon>
                            </span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ex: Jean Dupont"
                                   class="w-full h-14 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 transition-all">
                        </div>
                        @error('name') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Adresse Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:mail" width="18"></iconify-icon>
                            </span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="jean.dupont@exemple.com"
                                   class="w-full h-14 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 transition-all">
                        </div>
                        @error('email') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div class="space-y-2 text-violet-500/80">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nouveau mot de passe</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:lock" width="18"></iconify-icon>
                            </span>
                            <input type="password" name="password" placeholder="Laissez vide pour conserver"
                                   class="w-full h-14 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 transition-all">
                        </div>
                        <p class="text-[10px] font-bold italic px-1 opacity-70 italic">Laissez vide pour ne pas modifier le mot de passe actuel.</p>
                        @error('password') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Confirmation</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:check-circle" width="18"></iconify-icon>
                            </span>
                            <input type="password" name="password_confirmation" placeholder="••••••••"
                                   class="w-full h-14 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 transition-all">
                        </div>
                    </div>

                    <!-- School Selection -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">École d'affectation</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="mdi:school" width="18"></iconify-icon>
                            </span>
                            <select name="school_id" 
                                    class="w-full h-14 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 appearance-none transition-all">
                                <option value="">Administrateur Global / National</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('school_id') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Roles -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Rôles / Privilèges</label>
                        <div class="flex flex-wrap gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl min-h-[56px]">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                           {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}
                                           class="w-5 h-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500 bg-white dark:bg-gray-700">
                                    <span class="text-xs font-black text-gray-600 dark:text-gray-300 uppercase tracking-tighter group-hover:text-violet-600 transition-colors">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-indigo-600 px-12 py-5 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1">
                        <iconify-icon icon="lucide:save" width="20"></iconify-icon>
                        ENREGISTRER LES MODIFICATIONS
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>
