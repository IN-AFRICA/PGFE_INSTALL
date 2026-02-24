<x-layouts.modules-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Activités scolaires', 'url' => route('admin.activities.index')], ['label' => 'Nouvelle activité', 'url' => '#']]">
    <div class="max-w-3xl mx-auto space-y-8">
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <iconify-icon icon="lucide:party-popper" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 dark:text-white">Nouvelle activité scolaire</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Déclarez un événement (sortie, visite, campagne...).</p>
                </div>
            </div>
            <a href="{{ route('admin.activities.index') }}" class="h-12 px-6 flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-black text-xs uppercase tracking-widest">
                <iconify-icon icon="lucide:arrow-left" width="18"></iconify-icon>
                RETOUR
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.activities.store') }}" method="POST" class="p-10 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Titre</label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="Ex: Journée portes ouvertes" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-emerald-600" />
                        @error('label') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 py-3 focus:ring-2 focus:ring-emerald-600" placeholder="Détaillez brièvement l'activité...">{{ old('description') }}</textarea>
                        @error('description') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Lieu</label>
                        <input type="text" name="place" value="{{ old('place') }}" placeholder="Ex: Salle polyvalente" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-emerald-600" />
                        @error('place') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Quantité / Budget</label>
                        <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-emerald-600" />
                        @error('quantity') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Date de début</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-emerald-600" />
                        @error('start_date') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">Date de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full h-12 rounded-2xl bg-gray-50 dark:bg-gray-800 border-none font-bold text-gray-800 dark:text-white px-4 focus:ring-2 focus:ring-emerald-600" />
                        @error('end_date') <p class="text-xs text-rose-500 font-bold px-1 italic">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50 dark:border-gray-800 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-emerald-600 px-10 py-4 text-sm font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                        <iconify-icon icon="lucide:save" width="20"></iconify-icon>
                        ENREGISTRER
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.modules-layout>
