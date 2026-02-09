<x-layouts.backend-layout :breadcrumbs="[['title' => 'États', 'url' => route('admin.infra-etats.index')], ['title' => 'Signalement']]">
    <div class="max-w-2xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-600/20">
                    <iconify-icon icon="lucide:alert-triangle" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Signaler un État</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Mise à jour du statut d'une infrastructure.</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-etats.store') }}" method="POST" class="p-10 space-y-8">
                @csrf
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Infrastructure concernée</label>
                        <select name="infra_infrastructure_id" required class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-amber-500 font-bold appearance-none transition-all">
                            <option value="">Sélectionner une infrastructure</option>
                            @foreach($infrastructures as $infra)
                                <option value="{{ $infra->id }}">{{ $infra->name }} ({{ $infra->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">État (Résumé)</label>
                        <input type="text" name="name" required placeholder="ex: Toiture endommagée, Peinture rafraîchie..." 
                            class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-amber-500 font-bold transition-all" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Détails de l'observation</label>
                        <textarea name="description" rows="5" 
                            class="w-full p-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-amber-500 font-bold transition-all" 
                            placeholder="Précisez la nature des dégradations ou des travaux effectués..."></textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-etats.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-amber-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-amber-600/20 hover:bg-amber-700 hover:-translate-y-1 transition-all">
                        Enregistrer le statut
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>