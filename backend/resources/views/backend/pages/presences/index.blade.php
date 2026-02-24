<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="space-y-6">
            @include('backend.pages.students.partials.operations-nav')

            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        <div class="flex items-center gap-4 flex-grow max-w-xl">
                            <div class="relative flex-grow">
                                <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" width="18"></iconify-icon>
                                <input type="text" placeholder="Recherche élève..." class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-100 bg-gray-50/50 text-sm focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto -mx-8">
                        <table class="min-w-full text-[11px] font-medium">
                            <thead class="border-y border-gray-50 text-gray-400 uppercase tracking-[0.05em] font-black">
                                <tr>
                                    <th class="px-8 py-4 text-left">Date</th>
                                    <th class="px-3 py-4 text-left">Élève</th>
                                    <th class="px-3 py-4 text-left">Matricule</th>
                                    <th class="px-3 py-4 text-left">Classe</th>
                                    <th class="px-3 py-4 text-left">École</th>
                                    <th class="px-3 py-4 text-left">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($presences as $presence)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-4 text-gray-500 uppercase">{{ optional($presence->created_at)->format('d/m/Y') }}</td>
                                        <td class="px-3 py-4">
                                            <span class="font-black text-gray-700 uppercase">
                                                {{ trim(($presence->student?->firstname ?? '').' '.($presence->student?->lastname ?? '').' '.($presence->student?->name ?? '')) ?: 'Élève #'.$presence->student_id }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-gray-500">{{ $presence->student?->matricule ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-600 font-bold uppercase">{{ $presence->classroom->name ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-400 italic">{{ $presence->school->name ?? '—' }}</td>
                                        <td class="px-8 py-4">
                                            @php
                                                $presenceLabel = $presence->presence ? 'Présent' : 'Absent';
                                                $statusColor = $presence->presence ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                                            @endphp
                                            <span class="inline-flex items-center px-4 py-1.5 rounded-full {{ $statusColor }} font-black uppercase text-[9px] tracking-widest border border-current opacity-80">
                                                {{ $presenceLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-8 py-20 text-center text-gray-400">
                                            Aucune présence enregistrée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($presences->hasPages())
                        <div class="mt-8">
                            {{ $presences->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
