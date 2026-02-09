<x-layouts.backend-layout :breadcrumbs="[['title' => 'Partenaires Infra', 'url' => route('admin.infra-bailleurs.index')], ['title' => 'Nouveau']]">
    <div class="max-w-2xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/20">
                    <iconify-icon icon="lucide:handshake" width="32"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white">Nouveau Partenaire</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Source de financement des infrastructures.</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <form action="{{ route('admin.infra-bailleurs.store') }}" method="POST" class="p-10 space-y-8">
                @csrf
                
                <div class="space-y-4">
                    <label class="text-sm font-black text-gray-700 dark:text-gray-300 ml-1">Nom du bailleur / partenaire</label>
                    <input type="text" name="name" required placeholder="ex: Banque Mondiale, ENABEL, Fonds Propres..." 
                        class="w-full h-14 px-5 rounded-2xl border-gray-100 dark:border-gray-800 dark:bg-gray-950 focus:ring-emerald-500 font-bold transition-all" />
                    @error('name')
                        <p class="text-rose-500 text-xs font-bold mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50 dark:border-gray-800 mt-10">
                    <a href="{{ route('admin.infra-bailleurs.index') }}" class="px-8 py-4 rounded-2xl text-gray-500 font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"> Annuler </a>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-emerald-600 text-white font-black uppercase text-xs tracking-widest shadow-xl shadow-emerald-600/20 hover:bg-emerald-700 hover:-translate-y-1 transition-all">
                        Enregistrer le partenaire
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.backend-layout>