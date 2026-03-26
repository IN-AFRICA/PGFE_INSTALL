@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Sortie #{{ $exit->id }}" subtitle="Détail du mouvement" icon="lucide:arrow-up-circle"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-exits.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-exits.edit', $exit) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="admin-data-card space-y-3 p-6 text-sm">
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Article</span>
                <span class="font-semibold">{{ $exit->article->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Quantité</span>
                <span class="font-bold text-rose-700">-{{ $exit->quantity }}</span>
            </div>
            <div class="flex justify-between border-b border-zinc-100 py-2">
                <span class="text-zinc-500">Date</span>
                <span>{{ $exit->exit_date ? \Illuminate\Support\Carbon::parse($exit->exit_date)->format('d/m/Y') : '—' }}</span>
            </div>
            @if ($exit->reason)
                <div class="pt-2">
                    <p class="admin-label">Motif</p>
                    <p class="whitespace-pre-wrap text-zinc-700">{{ $exit->reason }}</p>
                </div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
