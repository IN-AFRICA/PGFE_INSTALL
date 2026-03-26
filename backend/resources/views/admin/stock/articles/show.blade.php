@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="{{ $article->name }}" subtitle="Fiche article" icon="lucide:package"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-articles.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-articles.edit', $article) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="admin-data-card space-y-4 p-6">
                <p class="admin-form-section-title">Informations</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-zinc-100 pb-2">
                        <dt class="text-zinc-500">Catégorie</dt>
                        <dd class="font-semibold text-zinc-900">{{ $article->category->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-zinc-100 pb-2">
                        <dt class="text-zinc-500">Fournisseur</dt>
                        <dd class="font-semibold text-zinc-900">{{ $article->provider->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-zinc-100 pb-2">
                        <dt class="text-zinc-500">Quantité</dt>
                        <dd class="font-bold text-zinc-900">{{ $article->quantity }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">Seuils min / max</dt>
                        <dd class="text-zinc-900">{{ $article->min_threshold ?? '—' }} / {{ $article->max_threshold ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="admin-data-card p-6">
                <form action="{{ route('admin.stock-articles.destroy', $article) }}" method="POST"
                    onsubmit="return confirm('Supprimer cet article ?')">
                    @csrf
                    @method('DELETE')
                    <p class="mb-4 text-sm text-zinc-500">La suppression est définitive (soft delete si configuré).</p>
                    <button type="submit" class="admin-btn-secondary border-red-200 text-red-700 hover:bg-red-50">Supprimer
                        l'article</button>
                </form>
            </div>
        </div>
    </x-admin.shadcn-shell>
@endsection
