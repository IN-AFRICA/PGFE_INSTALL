<x-layouts.backend-layout :breadcrumbs="[['title' => 'Inventaires', 'url' => route('admin.infra-inventaires.index')], ['title' => 'Édition']]">
    <div class="max-w-2xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <iconify-icon icon="lucide:clipboard-edit" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Modifier l'Inventaire</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Mise à jour d'une fiche de suivi d'équipement.</p>
                </div>
            </div>
            <a href="{{ route('admin.infra-inventaires.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-gray-100 transition-all">
                <iconify-icon icon="lucide:x" width="24"></iconify-icon>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-inventaires.update', $inventaire->id) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Équipement concerné</label>
                        <select name="equipement_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold appearance-none transition-all">
                            <option value="">Choisir un équipement</option>
                            @foreach($equipements as $equip)
                                <option value="{{ $equip->id }}" @selected(old('equipement_id', $inventaire->equipement_id) == $equip->id)>
                                    {{ $equip->name }} ({{ $equip->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Observation / État actuel</label>
                        <textarea name="observation" rows="5" 
                            class="w-full p-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all" 
                            placeholder="Notez ici l'état physique, le besoin de maintenance ou toute autre information pertinente...">{{ old('observation', $inventaire->observation) }}</textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-inventaires.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>