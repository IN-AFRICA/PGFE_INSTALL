<x-layouts.backend-layout :breadcrumbs="[['title' => 'Inventaires Infrastructure', 'url' => route('admin.infra-infrastructure-inventaires.index')], ['title' => 'Édition']]">
    <div class="max-w-3xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <iconify-icon icon="lucide:pen-tool" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Modifier l'Inventaire</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Mise à jour du rapport d'évaluation.</p>
                </div>
            </div>
            <a href="{{ route('admin.infra-infrastructure-inventaires.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-gray-100 transition-all">
                <iconify-icon icon="lucide:x" width="24"></iconify-icon>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-infrastructure-inventaires.update', $inventaire->id) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Titre de l'inventaire</label>
                        <input type="text" name="title" required placeholder="ex: Audit Trimestriel - Bâtiment Administratif" 
                            class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all" value="{{ old('title', $inventaire->title) }}" />
                        @error('title') <p class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Infrastructure</label>
                        <select name="infra_infrastructure_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold appearance-none transition-all">
                            <option value="">Sélectionner bâtiment</option>
                            @foreach($infrastructures as $infra)
                                <option value="{{ $infra->id }}" @selected(old('infra_infrastructure_id', $inventaire->infra_infrastructure_id) == $infra->id)>{{ $infra->name }} ({{ $infra->code }})</option>
                            @endforeach
                        </select>
                        @error('infra_infrastructure_id') <p class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Date d'inventaire</label>
                        <input type="date" name="inventory_date" required 
                            class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all" value="{{ old('inventory_date', $inventaire->inventory_date->format('Y-m-d')) }}" />
                        @error('inventory_date') <p class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">État global</label>
                        <select name="status" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold appearance-none transition-all">
                            <option value="excellent" @selected(old('status', $inventaire->status) == 'excellent')>Excellent</option>
                            <option value="bon" @selected(old('status', $inventaire->status) == 'bon')>Bon</option>
                            <option value="moyen" @selected(old('status', $inventaire->status) == 'moyen')>Moyen (Usure normale)</option>
                            <option value="mauvais" @selected(old('status', $inventaire->status) == 'mauvais')>Mauvais (Réparation nécessaire)</option>
                            <option value="critique" @selected(old('status', $inventaire->status) == 'critique')>Critique (Danger/Inutilisable)</option>
                        </select>
                        @error('status') <p class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Description / Observations générales</label>
                        <textarea name="description" rows="4" 
                            class="w-full p-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all" 
                            placeholder="Détails sur l'état des lieux, les besoins de maintenance vus lors de la visite...">{{ old('description', $inventaire->description) }}</textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-infrastructure-inventaires.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                        Mettre à jour l'inventaire
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>
