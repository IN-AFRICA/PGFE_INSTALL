<x-layouts.backend-layout :breadcrumbs="[['title' => 'Stock', 'url' => route('admin.stock-articles.index')], ['title' => 'Nouvel Article']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                    <iconify-icon icon="lucide:package-plus" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Nouvel Article</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Ajout d'une nouvelle référence à l'inventaire.</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.stock-articles.store') }}" method="POST" class="p-10 space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Basic Info -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em] mb-4">Informations Produit</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Désignation de l'article</label>
                            <input type="text" name="name" required placeholder="ex: Rame de papier A4 80g" 
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Catégorie</label>
                            <select name="category_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold appearance-none transition-all">
                                <option value="">Choisir une catégorie</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Fournisseur Habituel</label>
                            <select name="provider_id" class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold appearance-none transition-all">
                                <option value="">DIVERS</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Quantities -->
                    <div class="space-y-6">
                        <h2 class="text-xs font-black text-indigo-500 uppercase tracking-[0.2em] mb-4">Gestion des Stocks</h2>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Quantité Initiale</label>
                            <input type="number" name="quantity" required min="0" value="0"
                                class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-black text-2xl text-emerald-600 transition-all" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Seuil Alerte (Min)</label>
                                <input type="number" name="min_threshold" value="5"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all text-rose-500" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Seuil Max</label>
                                <input type="number" name="max_threshold" value="100"
                                    class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-indigo-500 font-bold transition-all text-blue-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.stock-articles.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-indigo-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                        Enregistrer l'article
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>