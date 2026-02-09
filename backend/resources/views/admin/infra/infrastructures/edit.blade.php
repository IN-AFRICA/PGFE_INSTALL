<x-layouts.backend-layout :breadcrumbs="[['title' => 'Infrastructures', 'url' => route('admin.infra-infrastructures.index')], ['title' => 'Édition']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-slate-600 flex items-center justify-center text-white shadow-lg shadow-slate-600/20">
                    <iconify-icon icon="lucide:pen-tool" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Modifier : {{ $infrastructure->name }}</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Mise à jour des informations techniques de l'ouvrage.</p>
                </div>
            </div>
            <a href="{{ route('admin.infra-infrastructures.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-gray-100 transition-all">
                <iconify-icon icon="lucide:x" width="24"></iconify-icon>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-infrastructures.update', $infrastructure->id) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Info -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Informations Générales</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Nom de l'ouvrage</label>
                            <input type="text" name="name" required value="{{ old('name', $infrastructure->name) }}" placeholder="ex: Bâtiment Administratif A" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Code / Identifiant</label>
                            <input type="text" name="code" value="{{ old('code', $infrastructure->code) }}" placeholder="ex: INF-001" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Emplacement / Adresse</label>
                            <input type="text" name="emplacement" value="{{ old('emplacement', $infrastructure->emplacement) }}" placeholder="ex: Aile Nord, Campus Principal" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" />
                        </div>
                    </div>

                    <!-- Classification & Finance -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Classification & Financement</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Catégorie</label>
                            <select name="infra_categorie_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold appearance-none transition-all">
                                <option value="">Sélectionner une catégorie</option>
                                @foreach(\App\Models\InfraCategorie::all() as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('infra_categorie_id', $infrastructure->infra_categorie_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Bailleur de fonds</label>
                            <select name="infra_bailleur_id" class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold appearance-none transition-all">
                                <option value="">Fonds Propres</option>
                                @foreach(\App\Models\InfraBailleur::all() as $bailleur)
                                    <option value="{{ $bailleur->id }}" @selected(old('infra_bailleur_id', $infrastructure->infra_bailleur_id) == $bailleur->id)>{{ $bailleur->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Date construction</label>
                                <input type="date" name="date_construction" value="{{ old('date_construction', $infrastructure->date_construction) }}"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Montant (USD)</label>
                                <input type="number" name="montant_construction" step="0.01" value="{{ old('montant_construction', $infrastructure->montant_construction) }}"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Description / Notes additionnelles</label>
                    <textarea name="description" rows="4" 
                        class="w-full p-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-slate-500 font-bold transition-all" 
                        placeholder="Précisions sur l'état, l'usage ou les travaux à prévoir...">{{ old('description', $infrastructure->description) }}</textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-infrastructures.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-slate-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-slate-600/20 hover:bg-slate-700 hover:-translate-y-1 transition-all">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>