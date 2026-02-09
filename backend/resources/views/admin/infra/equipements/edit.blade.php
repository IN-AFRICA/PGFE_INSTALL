<x-layouts.backend-layout :breadcrumbs="[['title' => 'Équipements', 'url' => route('admin.infra-equipements.index')], ['title' => 'Édition']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                    <iconify-icon icon="lucide:monitor-smartphone" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Modifier l'Équipement</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Édition des détails du matériel.</p>
                </div>
            </div>
            <a href="{{ route('admin.infra-equipements.index') }}" class="h-12 w-12 flex items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-gray-100 transition-all">
                <iconify-icon icon="lucide:x" width="24"></iconify-icon>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-equipements.update', $equipement->id) }}" method="POST" class="p-10 space-y-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Info -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-blue-500 uppercase tracking-[0.2em] mb-4">Détails du Matériel</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Désignation</label>
                            <input type="text" name="name" required value="{{ old('name', $equipement->name) }}" placeholder="ex: Bureau d'écolier" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold transition-all" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Code Inventaire / Série</label>
                            <input type="text" name="code" value="{{ old('code', $equipement->code) }}" placeholder="ex: EQ-2024-001" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold transition-all" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Emplacement précis</label>
                            <input type="text" name="emplacement" value="{{ old('emplacement', $equipement->emplacement) }}" placeholder="ex: Salle 4B" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold transition-all" />
                        </div>
                    </div>

                    <!-- Classification & Finance -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-blue-500 uppercase tracking-[0.2em] mb-4">Classification & Origine</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Type d'équipement</label>
                            <select name="categorie_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold appearance-none transition-all">
                                <option value="">Choisir un type</option>
                                @foreach(\App\Models\InfraCategorie::all() as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('categorie_id', $equipement->categorie_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Partenaire Financeur</label>
                            <select name="bailleur_id" class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold appearance-none transition-all">
                                <option value="">Don / Propre</option>
                                @foreach(\App\Models\InfraBailleur::all() as $bailleur)
                                    <option value="{{ $bailleur->id }}" @selected(old('bailleur_id', $equipement->bailleur_id) == $bailleur->id)>{{ $bailleur->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Date acquisition</label>
                                <input type="date" name="date_acquisition" value="{{ old('date_acquisition', $equipement->date_acquisition) }}"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold transition-all" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Valeur (USD)</label>
                                <input type="number" name="montant_acquisition" step="0.01" value="{{ old('montant_acquisition', $equipement->montant_acquisition) }}"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-blue-500 font-bold transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-equipements.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-blue-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700 hover:-translate-y-1 transition-all">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>