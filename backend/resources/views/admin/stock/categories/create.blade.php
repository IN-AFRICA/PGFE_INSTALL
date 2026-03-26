@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Nouvelle catégorie" subtitle="Organiser les articles par famille."
        icon="lucide:tag" breadcrumb-current="Création">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-categories.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-categories.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="admin-label" for="name">Nom</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="admin-input"
                        placeholder="ex. Papeterie">
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-categories.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
