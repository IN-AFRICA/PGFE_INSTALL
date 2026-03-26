@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Modifier l'article" :subtitle="$article->name" icon="lucide:package"
        breadcrumb-current="Modifier">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-articles.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-articles.update', $article) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                <div class="grid gap-8 md:grid-cols-2">
                    <div class="space-y-5">
                        <p class="admin-form-section-title">Produit</p>
                        <div>
                            <label class="admin-label" for="name">Désignation</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $article->name) }}" required
                                class="admin-input">
                        </div>
                        <div>
                            <label class="admin-label" for="category_id">Catégorie</label>
                            <select id="category_id" name="category_id" required class="admin-select">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $article->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="admin-label" for="provider_id">Fournisseur</label>
                            <select id="provider_id" name="provider_id" class="admin-select">
                                <option value="">— Aucun —</option>
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->id }}" @selected(old('provider_id', $article->provider_id) == $provider->id)>{{ $provider->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <p class="admin-form-section-title">Quantités</p>
                        <div>
                            <label class="admin-label" for="quantity">Quantité en stock</label>
                            <input id="quantity" type="number" name="quantity" min="0"
                                value="{{ old('quantity', $article->quantity) }}" required class="admin-input">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="admin-label" for="min_threshold">Seuil min</label>
                                <input id="min_threshold" type="number" name="min_threshold" min="0"
                                    value="{{ old('min_threshold', $article->min_threshold) }}" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label" for="max_threshold">Seuil max</label>
                                <input id="max_threshold" type="number" name="max_threshold" min="0"
                                    value="{{ old('max_threshold', $article->max_threshold) }}" class="admin-input">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-articles.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
