<x-layouts.backend-layout :breadcrumbs="[['title' => 'Bailleurs']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/20">
                        <iconify-icon icon="lucide:hand-coins" width="28"></iconify-icon>
                    </div>
                    Partenaires Financiers
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Gestion des bailleurs de fonds et sources de financement.</p>
            </div>
            <a href="{{ route('admin.infra-bailleurs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-8 py-4 text-sm font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEAU BAILLEUR
            </a>
        </div>

        <!-- Bailleurs List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Nom de l'Organisme</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($bailleurs as $bailleur)
                            <tr class="group hover:bg-emerald-50/20 dark:hover:bg-emerald-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 font-black text-sm">{{ $bailleur->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 font-black text-lg group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                           {{ mb_substr($bailleur->name, 0, 1) }}
                                        </div>
                                        <span class="font-black text-xl text-gray-800 dark:text-white group-hover:text-emerald-600 transition-colors">{{ $bailleur->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.infra-bailleurs.edit', $bailleur->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-bailleurs.destroy', $bailleur->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
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
                                <td colspan="3" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:wallet-cards" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun partenaire financier enregistré.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($bailleurs->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $bailleurs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>