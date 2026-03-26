@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Modifier la sortie" subtitle="Mouvement #{{ $exit->id }}" icon="lucide:arrow-up-circle"
        breadcrumb-current="Modifier">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-exits.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-exits.update', $exit) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="article_id">Article</label>
                    <select id="article_id" name="article_id" required class="admin-select">
                        @foreach ($articles as $a)
                            <option value="{{ $a->id }}" @selected(old('article_id', $exit->article_id) == $a->id)>
                                {{ $a->name }} (stock : {{ $a->quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="quantity">Quantité</label>
                    <input id="quantity" type="number" name="quantity" min="1" value="{{ old('quantity', $exit->quantity) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="exit_date">Date</label>
                    <input id="exit_date" type="date" name="exit_date"
                        value="{{ old('exit_date', \Illuminate\Support\Carbon::parse($exit->exit_date)->format('Y-m-d')) }}"
                        required class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="reason">Motif</label>
                    <textarea id="reason" name="reason" class="admin-textarea" rows="3">{{ old('reason', $exit->reason) }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-exits.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
