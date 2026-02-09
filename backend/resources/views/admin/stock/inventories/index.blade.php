<x-layouts.backend-layout :breadcrumbs="[['title' => 'Stock', 'url' => route('admin.stock-articles.index')], ['title' => 'Inventaires']]">
    <div class="max-w-6xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-violet-600 flex items-center justify-center text-white shadow-lg shadow-violet-600/20">
                        <iconify-icon icon="lucide:clipboard-check" width="28"></iconify-icon>
                    </div>
                    Sessions d'Inventaire
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Réconciliation périodique du stock théorique et du stock réel.</p>
            </div>
            <a href="{{ route('admin.stock-inventories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-8 py-4 text-sm font-black text-white hover:bg-violet-700 shadow-xl shadow-violet-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEL INVENTAIRE
            </a>
        </div>

        <!-- Inventories List -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Titre / Référence</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Date de Session</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($inventories as $inv)
                            <tr class="group hover:bg-violet-50/20 dark:hover:bg-violet-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-50 dark:bg-violet-900/40 text-violet-600 font-black text-sm">{{ $inv->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-lg text-gray-800 dark:text-white">{{ $inv->title ?? 'Inventaire sans titre' }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <iconify-icon icon="lucide:calendar" width="16"></iconify-icon>
                                        <span class="font-bold">{{ \Carbon\Carbon::parse($inv->inventory_date)->format('d F Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.stock-inventories.edit', $inv->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-violet-600 hover:text-white transition-all">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-inventories.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Supprimer cet inventaire ?')" class="inline">
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
                                <td colspan="4" class="px-8 py-20 text-center text-gray-400 font-bold uppercase tracking-widest text-xs">
                                    Aucune session d'inventaire enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($inventories, 'links'))
                <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800">
                    {{ $inventories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>