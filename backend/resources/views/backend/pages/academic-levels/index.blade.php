<x-layouts.backend-layout :breadcrumbs="[['title' => 'Niveaux Académiques']]">
    <div class="space-y-10">
        <!-- Premium Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-cyan-600 flex items-center justify-center text-white shadow-lg shadow-cyan-600/20">
                        <iconify-icon icon="lucide:graduation-cap" width="28"></iconify-icon>
                    </div>
                    Niveaux Académiques
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Définition des paliers d'enseignement (ex: 7e, 8e, Humanités).</p>
            </div>
            <a href="{{ route('admin.academic-levels.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-8 py-4 text-sm font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                AJOUTER UN NIVEAU
            </a>
        </div>

        <!-- Levels List -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest w-20">Rang</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Désignation</th>
                             <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($levels as $l)
                            <tr class="group hover:bg-cyan-50/20 dark:hover:bg-cyan-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-cyan-50 text-cyan-600 font-black text-xs">{{ $l->id }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-gray-800 dark:text-white text-lg group-hover:text-cyan-600 transition-colors">{{ $l->name }}</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.academic-levels.edit', $l) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-cyan-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.academic-levels.destroy', $l) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')" class="inline">
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
                                        <iconify-icon icon="lucide:graduation-cap" class="text-gray-200" width="64"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucun niveau académique configuré.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($levels->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $levels->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
