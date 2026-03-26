@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Nouvel inventaire" subtitle="Ouvre une session d'inventaire (lignes articles via l'API ou modules futurs)." icon="lucide:clipboard-check"
        breadcrumb-current="Création">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-inventories.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-inventories.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="admin-label" for="inventory_date">Date de l'inventaire</label>
                    <input id="inventory_date" type="date" name="inventory_date"
                        value="{{ old('inventory_date', now()->format('Y-m-d')) }}" required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="note">Note / contexte</label>
                    <textarea id="note" name="note" class="admin-textarea" rows="4"
                        placeholder="Campus, local, période…">{{ old('note') }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-inventories.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
