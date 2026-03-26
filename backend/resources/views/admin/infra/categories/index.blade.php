@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="infra" title="Catégories d'infrastructures"
        subtitle="Salles, blocs sanitaires, bureaux, etc." icon="lucide:tag" breadcrumb-current="Catégories">
        <x-slot name="actions">
            <a href="{{ route('admin.infra-categories.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                Nouvelle catégorie
            </a>
        </x-slot>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $category)
                <div
                    class="admin-data-card group p-6 transition-all duration-300 hover:border-zinc-300 hover:shadow-md">
                    <div class="mb-4 flex items-start justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-100 bg-zinc-50 text-zinc-400 transition-colors group-hover:border-zinc-200 group-hover:bg-zinc-900 group-hover:text-white">
                            <iconify-icon icon="lucide:tag" width="20"></iconify-icon>
                        </div>
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">#{{ $category->id }}</span>
                    </div>

                    <h3 class="mb-2 text-lg font-bold tracking-tight text-zinc-900">{{ $category->name }}</h3>

                    <div class="mt-6 flex items-center gap-2 border-t border-zinc-100 pt-4">
                        <a href="{{ route('admin.infra-categories.edit', $category) }}"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-bold text-zinc-700 transition-colors hover:bg-zinc-50">
                            <iconify-icon icon="lucide:edit-3" width="14"></iconify-icon>
                            Éditer
                        </a>
                        <form action="{{ route('admin.infra-categories.destroy', $category) }}" method="POST"
                            class="flex-1" onsubmit="return confirm('Confirmer la suppression ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-100 bg-red-50/80 px-3 py-2 text-xs font-bold text-red-700 transition-colors hover:bg-red-100">
                                <iconify-icon icon="lucide:trash-2" width="14"></iconify-icon>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-data-card col-span-full py-16 text-center">
                    <iconify-icon icon="lucide:inbox" class="mx-auto mb-3 text-zinc-200" width="48"></iconify-icon>
                    <p class="font-semibold text-zinc-600">Aucune catégorie</p>
                    <p class="mt-1 text-sm text-zinc-400">Créez une première catégorie pour classer vos ouvrages.</p>
                </div>
            @endforelse
        </div>
    </x-admin.shadcn-shell>
@endsection
