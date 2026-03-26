@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Entrées de stock" subtitle="Arrivages et réapprovisionnements."
        icon="lucide:arrow-down-circle" breadcrumb-current="Entrées">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-entries.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                Nouvelle entrée
            </a>
        </x-slot>

        <div class="admin-data-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">ID</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Article</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Qté</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Date</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($entries as $entry)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ $entry->id }}</td>
                                <td class="px-4 py-3 font-medium text-zinc-900">{{ $entry->article->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-emerald-700">+{{ $entry->quantity }}</td>
                                <td class="px-4 py-3 text-zinc-600">
                                    {{ $entry->entry_date ? \Illuminate\Support\Carbon::parse($entry->entry_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.stock-entries.show', $entry) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:eye" width="16"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.stock-entries.edit', $entry) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:pen-line" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-entries.destroy', $entry) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Supprimer cette entrée ? Le stock sera ajusté.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                                <iconify-icon icon="lucide:trash-2" width="16"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center text-sm text-zinc-500">Aucune entrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($entries->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $entries->links() }}</div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
