<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2 italic">
                    <iconify-icon icon="lucide:scale" width="28" class="text-sky-500"></iconify-icon>
                    Délibérations
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Aperçu des délibérations par élève, cours et année scolaire.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-3">Élève</th>
                            <th class="px-6 py-3">Cours</th>
                            <th class="px-6 py-3">Classe</th>
                            <th class="px-6 py-3">Année</th>
                            <th class="px-6 py-3 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($deliberations as $delib)
                            <tr class="hover:bg-sky-50/40 dark:hover:bg-sky-900/10 transition-colors">
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    {{ trim(($delib->student->firstname ?? '').' '.($delib->student->lastname ?? '').' '.($delib->student->name ?? '')) ?: 'Élève #'.$delib->student_id }}
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200 font-medium">
                                    {{ $delib->course->label ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $delib->classroom->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300 text-xs">
                                    {{ $delib->schoolYear->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @php
                                        $validated = (bool) $delib->is_validated;
                                        $label = $validated ? 'Validée' : 'Non validée';
                                        $color = $validated
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                            : 'bg-amber-50 text-amber-700 border-amber-100';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $color }}">
                                        {{ $label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucune délibération trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($deliberations->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40">
                    {{ $deliberations->links() }}
                </div>
            @endif
        </div>
        </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
