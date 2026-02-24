<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2 italic">
                    <iconify-icon icon="lucide:eye" width="28" class="text-indigo-500"></iconify-icon>
                    Visites de classe
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Liste des visites pédagogiques pour l'école sélectionnée.</p>
            </div>
        </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-3">Date / Heure</th>
                            <th class="px-6 py-3">Classe</th>
                            <th class="px-6 py-3">Visiteur</th>
                            <th class="px-6 py-3">Objet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($visits as $visit)
                            <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors">
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    @php
                                        $vh = $visit->visit_hour;
                                    @endphp
                                    {{ $vh ? \Carbon\Carbon::parse($vh)->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $visit->classroom->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $visit->visiteur ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $visit->subject }}">
                                    {{ $visit->subject ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucune visite trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($visits->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40">
                    {{ $visits->links() }}
                </div>
            @endif
        </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
