@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Entrée #{{ $entry->id }}" subtitle="Détail du mouvement" icon="lucide:arrow-down-circle"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-entries.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-entries.edit', $entry) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="admin-data-card space-y-3 p-6 text-sm">
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Article</span>
                <span class="font-semibold">{{ $entry->article->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Quantité</span>
                <span class="font-bold text-emerald-700">+{{ $entry->quantity }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Date</span>
                <span>{{ $entry->entry_date ? \Illuminate\Support\Carbon::parse($entry->entry_date)->format('d/m/Y') : '—' }}</span>
            </div>
            @if ($entry->note)
                <div class="pt-2">
                    <p class="admin-label">Note</p>
                    <p class="text-zinc-700">{{ $entry->note }}</p>
                </div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
