<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <div class="space-y-6">
            @include('backend.pages.students.partials.operations-nav')

            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                        {{-- Search & Grouped Actions matches Image 1 --}}
                        <div class="flex items-center gap-4 flex-grow max-w-xl">
                            <div class="relative flex-grow">
                                <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" width="18"></iconify-icon>
                                <input type="text" placeholder="Recherche..." class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-100 bg-gray-50/50 text-sm focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none">
                            </div>
                            <button class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl border border-gray-100 bg-white text-[11px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 transition-all">
                                Filtre <iconify-icon icon="lucide:filter" width="14"></iconify-icon>
                            </button>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.registrations.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#0ea5e9] px-6 py-3.5 text-[11px] font-black text-white hover:bg-sky-600 shadow-lg shadow-sky-500/20 transition-all active:scale-95 uppercase tracking-widest">
                                <iconify-icon icon="lucide:plus" width="16" height="16" />
                                Nouveau Eleve
                            </a>
                            <div class="relative">
                                <button class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl border border-gray-100 bg-white text-[11px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 transition-all">
                                    Exporter <iconify-icon icon="lucide:chevron-down" width="14"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto -mx-8">
                        <table class="min-w-full text-[11px] font-medium">
                            <thead class="border-y border-gray-50 text-gray-400 uppercase tracking-[0.05em] font-black">
                                <tr>
                                    <th class="px-8 py-4 text-left w-10">
                                        <input type="checkbox" class="rounded-lg border-gray-100 text-sky-500 focus:ring-sky-500/20">
                                    </th>
                                    <th class="px-3 py-4 text-left">N°</th>
                                    <th class="px-3 py-4 text-left">Matricule</th>
                                    <th class="px-3 py-4 text-left">Nom</th>
                                    <th class="px-3 py-4 text-left">Postnom</th>
                                    <th class="px-3 py-4 text-left">Prénom</th>
                                    <th class="px-3 py-4 text-left">Pays</th>
                                    <th class="px-3 py-4 text-left">Sexe</th>
                                    <th class="px-3 py-4 text-left">Classe</th>
                                    <th class="px-3 py-4 text-left">Section</th>
                                    <th class="px-3 py-4 text-left">Date de naissance</th>
                                    <th class="px-3 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($registrations as $index => $r)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-4">
                                            <input type="checkbox" class="rounded-lg border-gray-100 text-sky-500 focus:ring-sky-500/20">
                                        </td>
                                        <td class="px-3 py-4 text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-3 py-4 font-black text-gray-600 uppercase">{{ $r->student->matricule ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-700 capitalize">{{ $r->student->lastname ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-700 capitalize">{{ $r->student->name ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-700 capitalize">{{ $r->student->firstname ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-500">{{ $r->student->country->name ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-500">{{ $r->student->gender ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-600 font-bold uppercase">{{ $r->classroom->name ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-500 capitalize">{{ $r->student->section ?? '—' }}</td>
                                        <td class="px-3 py-4 text-gray-500">{{ $r->student->birth_date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="px-8 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('admin.registrations.edit', $r) }}" class="h-8 w-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-amber-100 hover:text-amber-600 transition-all">
                                                    <iconify-icon icon="lucide:edit" width="14"></iconify-icon>
                                                </a>
                                                <form action="{{ route('admin.registrations.destroy', $r) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="h-8 w-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-rose-100 hover:text-rose-600 transition-all">
                                                        <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="px-8 py-20 text-center">
                                            <div class="flex flex-col items-center">
                                                <iconify-icon icon="lucide:user-x" class="text-gray-100 mb-4" width="64"></iconify-icon>
                                                <p class="text-gray-400 font-medium">Aucun élève trouvé.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $registrations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>
