@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="{{ $provider->name }}" subtitle="Fiche fournisseur" icon="lucide:truck"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-providers.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-providers.edit', $provider) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="admin-data-card space-y-4 p-6">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-zinc-100 pb-2">
                    <dt class="text-zinc-500">Contact</dt>
                    <dd class="text-right font-medium text-zinc-900">{{ $provider->contact ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="admin-label mb-1">Adresse</dt>
                    <dd class="text-zinc-700">{{ $provider->address ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </x-admin.shadcn-shell>
@endsection
