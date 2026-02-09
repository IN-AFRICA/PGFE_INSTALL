<x-layouts.backend-layout :breadcrumbs="[['title' => 'Inventaires Infra']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-lg shadow-teal-600/20">
                        <iconify-icon icon="lucide:clipboard-list" width="28"></iconify-icon>
                    </div>
                    Inventaires d'Infrastructures
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Suivi de l'état et présence des équipements et infrastructures.</p>
            </div>
            <a href="{{ route('admin.infra-inventaires.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-600 px-8 py-4 text-sm font-black text-white hover:bg-teal-700 shadow-xl shadow-teal-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                NOUVEL INVENTAIRE
            </a>
        </div>

        <!-- Inventaires List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Équipement / Infra</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Observation</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($inventaires as $inv)
                            <tr class="group hover:bg-teal-50/20 dark:hover:bg-teal-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 dark:bg-teal-900/40 text-teal-600 font-black text-sm">{{ $inv->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-lg text-gray-800 dark:text-white">{{ $inv->equipement->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $inv->equipement->code ?? 'SANS CODE' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-sm text-gray-500 font-medium">
                                    {{ Str::limit($inv->observation, 50) }}
                                </td>
                                <td class="px-8 py-6 text-sm text-gray-500 font-black">
                                    {{ $inv->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.infra-inventaires.edit', $inv->id) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-teal-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-inventaires.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
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
                                        <iconify-icon icon="lucide:clipboard-x" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun inventaire enregistré dans cette école.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($inventaires->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $inventaires->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>