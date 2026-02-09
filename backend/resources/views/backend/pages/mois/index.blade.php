<x-layouts.backend-layout :breadcrumbs="[['title' => 'Mois']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-600/20">
                        <iconify-icon icon="lucide:calendar-days" width="28"></iconify-icon>
                    </div>
                    Mois Scolaires
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Configuration des mois budgétaires et académiques.</p>
            </div>
            <a href="#" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-600 px-8 py-4 text-sm font-black text-white hover:bg-amber-700 shadow-xl shadow-amber-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                AJOUTER UN MOIS
            </a>
        </div>

        <!-- Months List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-24">Ordre</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Désignation</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($mois as $m)
                            <tr class="group hover:bg-amber-50/20 dark:hover:bg-amber-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 font-black text-sm">{{ $m->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-xl text-gray-800 dark:text-white group-hover:text-amber-600 transition-colors">{{ $m->name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <button disabled class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-300 cursor-not-allowed">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </button>
                                        <button disabled class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-300 cursor-not-allowed">
                                            <iconify-icon icon="lucide:trash-2" width="20"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:calendar-x-2" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun mois configuré.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
