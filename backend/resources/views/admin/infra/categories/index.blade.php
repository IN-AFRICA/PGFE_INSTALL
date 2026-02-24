<x-layouts.modules-layout>
    <x-backend.pages.infra.layout>
        <div class="space-y-10">
            <!-- Premium Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3 italic">
                        <div class="h-12 w-12 rounded-2xl bg-gray-600 flex items-center justify-center text-white shadow-lg shadow-gray-600/20">
                            <iconify-icon icon="lucide:layout-grid" width="28"></iconify-icon>
                        </div>
                        Catégories d'Infrastructures
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Classification des ouvrages (Salles de classe, Blocs sanitaires, Bureaux admin...).</p>
                </div>
                <a href="{{ route('admin.infra-categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gray-600 px-8 py-4 text-sm font-black text-white hover:bg-gray-700 shadow-xl shadow-gray-600/20 transition-all hover:-translate-y-1">
                    <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                    NOUVELLE CATÉGORIE
                </a>
            </div>

            <!-- Categories List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($categories as $category)
                    <div class="bg-white dark:bg-gray-900 p-10 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 group hover:shadow-2xl transition-all flex flex-col items-center text-center">
                        <div class="h-14 w-14 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-gray-600 group-hover:text-white transition-all mb-6">
                            <iconify-icon icon="lucide:tag" width="28"></iconify-icon>
                        </div>
                        <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2 uppercase">{{ $category->name }}</h3>
                        <p class="text-xs font-black text-gray-400 tracking-widest mb-6">#{{ $category->id }}</p>
                        
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.infra-categories.edit', $category) }}" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-amber-100 hover:text-amber-600 transition-all">
                                <iconify-icon icon="lucide:edit" width="18"></iconify-icon>
                            </a>
                            <form action="{{ route('admin.infra-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="h-10 w-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-rose-100 hover:text-rose-600 transition-all">
                                    <iconify-icon icon="lucide:trash-2" width="18"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-backend.pages.infra.layout>
</x-layouts.modules-layout>
