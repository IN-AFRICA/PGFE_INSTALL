@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Nouvel état de stock" subtitle="Enregistre une quantité constatée (ne modifie pas le stock théorique)." icon="lucide:clipboard-list"
        breadcrumb-current="Création">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-states.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-states.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="admin-label" for="article_id">Article</label>
                    <select id="article_id" name="article_id" required class="admin-select">
                        <option value="">— Choisir —</option>
                        @foreach ($articles as $a)
                            <option value="{{ $a->id }}" @selected(old('article_id') == $a->id)>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="quantity">Quantité constatée</label>
                    <input id="quantity" type="number" name="quantity" min="0" value="{{ old('quantity', 0) }}" required
                        class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="state_date">Date du constat</label>
                    <input id="state_date" type="date" name="state_date" value="{{ old('state_date', now()->format('Y-m-d')) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="note">Note</label>
                    <textarea id="note" name="note" class="admin-textarea" rows="3">{{ old('note') }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-states.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
