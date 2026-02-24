<x-layouts.modules-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Personnel', 'url' => route('admin.personnels.index')], ['label' => 'Modifier', 'url' => '#']]">
    <div class="max-w-3xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <iconify-icon icon="lucide:id-card" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white">Modifier le Personnel</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                        Mise à jour de la fiche pour
                        <span class="text-emerald-600">
                            {{ trim(($personnel->pre_name ?? '').' '.($personnel->post_name ?? '').' '.($personnel->name ?? 'Agent #'.$personnel->id)) }}
                        </span>.
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.personnels.index') }}" class="h-12 px-6 flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-black text-xs uppercase tracking-widest">
                <iconify-icon icon="lucide:arrow-left" width="18"></iconify-icon>
                RETOUR
            </a>
        </div>

        <!-- Edit Form -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.personnels.update', ['personnel' => $personnel->id]) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Prénom (pre_name) -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Prénom</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:user" width="18"></iconify-icon>
                            </span>
                            <input type="text" name="pre_name" value="{{ old('pre_name', $personnel->pre_name) }}" placeholder="Ex: Jean"
                                   class="w-full h-12 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-600 transition-all">
                        </div>
                        @error('pre_name') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Post-nom (post_name) -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Post-nom</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:user" width="18"></iconify-icon>
                            </span>
                            <input type="text" name="post_name" value="{{ old('post_name', $personnel->post_name) }}" placeholder="Ex: Pierre"
                                   class="w-full h-12 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-600 transition-all">
                        </div>
                        @error('post_name') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nom de famille (name) -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Nom de famille</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="lucide:user-square" width="18"></iconify-icon>
                            </span>
                            <input type="text" name="name" value="{{ old('name', $personnel->name) }}" placeholder="Ex: KABAMBA"
                                   class="w-full h-12 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-600 transition-all">
                        </div>
                        @error('name') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <!-- École d'affectation -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">École d'affectation</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <iconify-icon icon="mdi:school" width="18"></iconify-icon>
                            </span>
                            <select name="school_id"
                                    class="w-full h-12 pl-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-600 appearance-none transition-all">
                                <option value="">Administrateur Global / National</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ old('school_id', $personnel->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('school_id') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-emerald-600 px-10 py-4 text-sm font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                        <iconify-icon icon="lucide:save" width="20"></iconify-icon>
                        ENREGISTRER LES MODIFICATIONS
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.modules-layout>
