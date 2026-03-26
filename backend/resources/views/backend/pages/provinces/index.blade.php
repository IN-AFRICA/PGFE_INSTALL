@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <!-- Floating Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
            <div>
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3 tracking-tighter">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                        <iconify-icon icon="lucide:map-pin" width="28"></iconify-icon>
                    </div>
                    Gestion des Provinces
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">Définissez les provinces rattachées aux pays.</p>
            </div>
            <a href="{{ route('admin.provinces.create') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-8 py-4 text-sm font-black text-white hover:bg-violet-700 shadow-xl shadow-violet-600/20 transition-all hover:-translate-y-1">
                <iconify-icon icon="lucide:plus-circle" width="20"></iconify-icon>
                AJOUTER UNE PROVINCE
            </a>
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Nom de la Province</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">Pays de Rattachement</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($provinces as $p)
                            <tr class="group hover:bg-violet-50/20 dark:hover:bg-violet-900/10 transition-colors">
                                <td class="px-8 py-6 text-sm font-bold text-gray-400 italic">#{{ $p->id }}</td>
                                <td class="px-8 py-6">
                                    <span class="font-black text-gray-800 dark:text-white group-hover:text-violet-600 transition-colors uppercase tracking-tight">{{ $p->name }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                        <iconify-icon icon="lucide:globe" width="14" class="text-indigo-400"></iconify-icon>
                                        {{ $p->country->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <a href="{{ route('admin.provinces.edit', $p) }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-violet-600 hover:text-white transition-all shadow-sm">
                                            <iconify-icon icon="lucide:pen-line" width="20"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.provinces.destroy', $p) }}" method="POST" onsubmit="return confirm('Supprimer cette province ?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                <iconify-icon icon="lucide:trash-2" width="20"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <iconify-icon icon="lucide:map" class="text-gray-200" width="80"></iconify-icon>
                                        <p class="text-gray-400 font-bold">Aucune province enregistrée.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($provinces->hasPages())
                <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $provinces->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
