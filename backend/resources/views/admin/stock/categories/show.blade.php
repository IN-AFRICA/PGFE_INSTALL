@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="{{ $category->name }}" subtitle="Détail de la catégorie" icon="lucide:tag"
        :breadcrumb-extras="[['label' => 'Catégories', 'url' => route('admin.stock-categories.index')]]"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-categories.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-categories.edit', $category) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="admin-data-card space-y-4 p-6 md:p-8">
            <div>
                <p class="admin-label">Nom</p>
                <p class="text-lg font-semibold text-zinc-900">{{ $category->name }}</p>
            </div>
            <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                <form action="{{ route('admin.stock-categories.destroy', $category) }}" method="POST" class="inline"
                    onsubmit="return confirm('Supprimer cette catégorie ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="admin-btn-secondary border-red-200 text-red-700 hover:border-red-300 hover:bg-red-50">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-admin.shadcn-shell>
@endsection
