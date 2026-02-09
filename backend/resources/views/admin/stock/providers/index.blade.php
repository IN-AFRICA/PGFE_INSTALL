<x-layouts.backend-layout :breadcrumbs="[['title' => 'Stock', 'url' => route('admin.stock-articles.index')], ['title' => 'Fournisseurs']]">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                        <iconify-icon icon="lucide:truck" width="28"></iconify-icon>
                    </div>
                    Fournisseurs & Partenaires
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gérez vos sources d'approvisionnement.</p>
            </div>
            <a href="{{ route('admin.stock-providers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-8 py-4 text-sm font-black text-white hover:bg-blue-700 shadow-xl shadow-blue-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEAU FOURNISSEUR
            </a>
        </div>

        <!-- Providers List -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Nom / Raison Sociale</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($providers as $provider)
                            <tr class="group hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 font-black text-sm">{{ $provider->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-lg text-gray-800 dark:text-white">{{ $provider->name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.stock-providers.edit', $provider->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-blue-600 hover:text-white transition-all">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-providers.destroy', $provider->id) }}" method="POST" onsubmit="return confirm('Supprimer ce fournisseur ?')" class="inline">
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
                                    Aucun fournisseur enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($providers, 'links'))
                <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800">
                    {{ $providers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>