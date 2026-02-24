<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2 italic">
                    <iconify-icon icon="lucide:log-out" width="28" class="text-rose-500"></iconify-icon>
                    Sorties des élèves
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Historique des sorties (permissions) enregistrées.</p>
            </div>
        </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Heure</th>
                            <th class="px-6 py-3">Élève</th>
                            <th class="px-6 py-3">Filière</th>
                            <th class="px-6 py-3">Année scolaire</th>
                            <th class="px-6 py-3">Motif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($studentExits as $exit)
                            <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-900/10 transition-colors">
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $exit->date ? \Carbon\Carbon::parse($exit->date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $exit->exit_time ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    {{ trim(($exit->student->firstname ?? '').' '.($exit->student->lastname ?? '').' '.($exit->student->name ?? '')) ?: 'Élève #'.$exit->student_id }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $exit->filiere->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300 text-xs">
                                    {{ $exit->schoolYear->title ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $exit->motif }}">
                                    {{ $exit->motif ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucune sortie trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($studentExits->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40">
                    {{ $studentExits->links() }}
                </div>
            @endif
        </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
