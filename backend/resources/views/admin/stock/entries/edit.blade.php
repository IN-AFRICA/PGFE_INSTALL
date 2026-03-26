@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Modifier l'entrée" subtitle="Mouvement #{{ $entry->id }}" icon="lucide:arrow-down-circle"
        breadcrumb-current="Modifier">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-entries.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-entries.update', $entry) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="article_id">Article</label>
                    <select id="article_id" name="article_id" required class="admin-select">
                        @foreach ($articles as $a)
                            <option value="{{ $a->id }}" @selected(old('article_id', $entry->article_id) == $a->id)>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="quantity">Quantité</label>
                    <input id="quantity" type="number" name="quantity" min="1" value="{{ old('quantity', $entry->quantity) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="entry_date">Date</label>
                    <input id="entry_date" type="date" name="entry_date"
                        value="{{ old('entry_date', \Illuminate\Support\Carbon::parse($entry->entry_date)->format('Y-m-d')) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="note">Note</label>
                    <textarea id="note" name="note" class="admin-textarea" rows="3">{{ old('note', $entry->note) }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-entries.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
