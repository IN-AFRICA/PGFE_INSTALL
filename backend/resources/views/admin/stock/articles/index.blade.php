<x-layouts.backend-layout :breadcrumbs="[['title' => 'Stock Articles']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                        <iconify-icon icon="lucide:package" width="28"></iconify-icon>
                    </div>
                    Gestion des Stocks
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Suivez vos consommables, fournitures et marchandises.</p>
            </div>
            <a href="{{ route('admin.stock-articles.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEL ARTICLE
            </a>
        </div>

        <!-- Articles List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Désignation</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Catégorie</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Stock</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($articles as $art)
                            <tr class="group hover:bg-indigo-50/20 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 font-black text-sm">{{ $art->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-lg text-gray-800 dark:text-white">{{ $art->name }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $art->provider->name ?? 'DIVERS FOURNISSEURS' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-black text-gray-500 uppercase">
                                        {{ $art->category->name ?? 'GÉNÉRAL' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl font-black {{ $art->quantity <= $art->min_threshold ? 'text-rose-500' : 'text-emerald-500' }}">
                                            {{ $art->quantity }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Unités</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.stock-articles.edit', $art->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-articles.destroy', $art->id) }}" method="POST" onsubmit="return confirm('Supprimer cet article ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <iconify-icon icon="lucide:trash-2" width="20"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:box" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun article en stock.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($articles->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>