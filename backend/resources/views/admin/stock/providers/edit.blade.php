@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Modifier le fournisseur" :subtitle="$provider->name" icon="lucide:truck"
        breadcrumb-current="Modifier">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-providers.index') }}" class="admin-btn-secondary">Retour</a>
        </x-slot>

        <div class="admin-data-card max-w-xl p-6 md:p-8">
            @include('admin.partials.form-errors')
            <form action="{{ route('admin.stock-providers.update', $provider) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="name">Raison sociale</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $provider->name) }}" required
                        class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="contact">Contact</label>
                    <input id="contact" type="text" name="contact" value="{{ old('contact', $provider->contact) }}"
                        class="admin-input">
                </div>
                <div>
                    <label class="admin-label" for="address">Adresse</label>
                    <textarea id="address" name="address" rows="3" class="admin-textarea">{{ old('address', $provider->address) }}</textarea>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-zinc-100 pt-6">
                    <a href="{{ route('admin.stock-providers.index') }}" class="admin-btn-secondary">Annuler</a>
                    <button type="submit" class="admin-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </x-admin.shadcn-shell>
@endsection
