<x-layouts.backend-layout :breadcrumbs="[['title' => 'Stock', 'url' => route('admin.stock-articles.index')], ['title' => 'Catégories']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                        <iconify-icon icon="lucide:tags" width="28"></iconify-icon>
                    </div>
                    Catégories d'Articles
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Organisez votre inventaire par types de biens.</p>
            </div>
            <a href="{{ route('admin.stock-categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-8 py-4 text-sm font-black text-white hover:bg-amber-600 shadow-xl shadow-amber-500/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVELLE CATÉGORIE
            </a>
        </div>

        <!-- Categories List -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Nom de la Catégorie</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($categories as $cat)
                            <tr class="group hover:bg-amber-50/20 dark:hover:bg-amber-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 font-black text-sm">{{ $cat->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-lg text-gray-800 dark:text-white">{{ $cat->name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.stock-categories.edit', $cat->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-amber-500 hover:text-white transition-all">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all">
                                                <iconify-icon icon="lucide:trash-2" width="20"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Aucune catégorie définie.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($categories, 'links'))
                <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>