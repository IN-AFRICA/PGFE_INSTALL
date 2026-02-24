<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2 italic">
                    <iconify-icon icon="lucide:clipboard-list" width="28" class="text-amber-500"></iconify-icon>
                    Fiches de cotation
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Aperçu des dernières fiches de cotation synchronisées.</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40">
                <form method="GET" action="{{ route('admin.fiche-cotations.index') }}" class="flex-1 max-w-md">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <iconify-icon icon="lucide:search" class="w-4 h-4"></iconify-icon>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher un élève ou un cours..."
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                        />
                    </div>
                </form>
                <div class="flex items-center gap-2 ml-4">
                    <button type="button" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <iconify-icon icon="lucide:sliders-horizontal" class="w-3 h-3"></iconify-icon>
                        Filtre
                    </button>
                    <button type="button" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-semibold rounded-xl bg-amber-500 text-white shadow-sm hover:bg-amber-600">
                        Initialiser la fiche
                        <iconify-icon icon="lucide:chevron-down" class="w-3 h-3"></iconify-icon>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <iconify-icon icon="lucide:file-text" class="w-3 h-3"></iconify-icon>
                        Excel
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-3 w-1/3">Élève</th>
                            <th class="px-3 py-3 text-center">P1</th>
                            <th class="px-3 py-3 text-center">P2</th>
                            <th class="px-3 py-3 text-center">E1</th>
                            <th class="px-3 py-3 text-center">P3</th>
                            <th class="px-3 py-3 text-center">P4</th>
                            <th class="px-3 py-3 text-center">E2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($ficheCotations as $fiche)
                            @php
                                $rawNote = $fiche->note;
                                $noteArray = is_string($rawNote) ? json_decode($rawNote, true) : (is_array($rawNote) ? $rawNote : []);
                                $periodKeys = ['P1', 'P2', 'E1', 'P3', 'P4', 'E2'];
                            @endphp
                            <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-900/10 transition-colors">
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-200 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">
                                            {{ trim(($fiche->student->firstname ?? '').' '.($fiche->student->lastname ?? '').' '.($fiche->student->name ?? '')) ?: 'Élève #'.$fiche->student_id }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $fiche->student->matricule ?? '—' }}
                                            • {{ $fiche->classroom->name ?? '—' }}
                                            • {{ $fiche->course->label ?? '—' }}
                                            • {{ $fiche->schoolYear->name ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                @foreach($periodKeys as $key)
                                    @php $value = $noteArray[$key] ?? null; @endphp
                                    <td class="px-3 py-4 text-center text-gray-800 dark:text-gray-100 font-semibold text-xs">
                                        {{ $value !== null ? $value : '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucune fiche de cotation trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ficheCotations->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40">
                    {{ $ficheCotations->links() }}
                </div>
            @endif
        </div>
    </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
