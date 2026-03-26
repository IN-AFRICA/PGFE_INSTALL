@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="État #{{ $state->id }}" subtitle="Détail du relevé" icon="lucide:clipboard-list"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-states.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-states.edit', $state) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="admin-data-card space-y-3 p-6 text-sm">
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Article</span>
                <span class="font-semibold">{{ $state->article->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Quantité constatée</span>
                <span class="font-bold text-zinc-900">{{ $state->quantity }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Date</span>
                <span>{{ $state->state_date ? \Illuminate\Support\Carbon::parse($state->state_date)->format('d/m/Y') : '—' }}</span>
            </div>
            @if ($state->user)
                <div class="flex justify-between border-b border-zinc-100 py-2">
                    <span class="text-zinc-500">Enregistré par</span>
                    <span>{{ $state->user->name }}</span>
                </div>
            @endif
            @if ($state->note)
                <div class="pt-2">
                    <p class="admin-label">Note</p>
                    <p class="whitespace-pre-wrap text-zinc-700">{{ $state->note }}</p>
                </div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
