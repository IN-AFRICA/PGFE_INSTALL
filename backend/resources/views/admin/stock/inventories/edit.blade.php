@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Modifier l'inventaire" subtitle="Session #{{ $inventory->id }}" icon="lucide:clipboard-check"
        breadcrumb-current="Modifier">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-inventories.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-inventories.update', $inventory) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="inventory_date">Date de l'inventaire</label>
                    <input id="inventory_date" type="date" name="inventory_date"
                        value="{{ old('inventory_date', \Illuminate\Support\Carbon::parse($inventory->inventory_date)->format('Y-m-d')) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="note">Note / contexte</label>
                    <textarea id="note" name="note" class="admin-textarea" rows="4">{{ old('note', $inventory->note) }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-inventories.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
