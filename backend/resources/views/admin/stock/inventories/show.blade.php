@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Inventaire #{{ $inventory->id }}" subtitle="Détail de la session" icon="lucide:clipboard-check"
        breadcrumb-current="Détail">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-inventories.index') }}" class="admin-btn-secondary">Liste</a>
            <a href="{{ route('admin.stock-inventories.edit', $inventory) }}" class="admin-btn-primary">Modifier</a>
        </x-slot>

        <div class="space-y-6">
            <div class="admin-data-card space-y-3 p-6 text-sm">
                <div class="flex justify-between border-b border-zinc-100 py-2">
                    <span class="text-zinc-500">Date</span>
                    <span class="font-semibold">
                        {{ $inventory->inventory_date ? \Illuminate\Support\Carbon::parse($inventory->inventory_date)->translatedFormat('d F Y') : '—' }}
                    </span>
                </div>
                @if ($inventory->user)
                    <div class="flex justify-between border-b border-zinc-100 py-2">
                        <span class="text-zinc-500">Créé par</span>
                        <span>{{ $inventory->user->name }}</span>
                    </div>
                @endif
                @if ($inventory->note)
                    <div class="pt-2">
                        <p class="admin-label">Note</p>
                        <p class="whitespace-pre-wrap text-zinc-700">{{ $inventory->note }}</p>
                    </div>
                @endif
            </div>

            <div class="admin-data-card overflow-hidden">
                <h2 class="border-b border-zinc-100 px-4 py-3 text-sm font-semibold text-zinc-900">Lignes comptées</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50/80">
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Article</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Qté</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Remarque</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($inventory->articles as $line)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-zinc-900">{{ $line->article->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $line->quantity }}</td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $line->note ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-12 text-center text-sm text-zinc-500">Aucune ligne liée à cet inventaire.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-admin.shadcn-shell>
@endsection
