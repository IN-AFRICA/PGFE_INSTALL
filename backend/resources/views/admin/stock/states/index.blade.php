@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="États de stock" subtitle="Relevés et constats (quantités constatées)." icon="lucide:clipboard-list"
        breadcrumb-current="États">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-states.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                Nouvel état
            </a>
        </x-slot>

        <div class="admin-data-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">ID</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Article</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Qté constatée</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Note</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Date</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($states as $state)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ $state->id }}</td>
                                <td class="px-4 py-3 font-medium text-zinc-900">{{ $state->article->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-zinc-800">{{ $state->quantity }}</td>
                                <td class="max-w-[200px] truncate px-4 py-3 text-zinc-500">{{ $state->note ? \Illuminate\Support\Str::limit($state->note, 40) : '—' }}</td>
                                <td class="px-4 py-3 text-zinc-600">
                                    {{ $state->state_date ? \Illuminate\Support\Carbon::parse($state->state_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.stock-states.show', $state) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:eye" width="16"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.stock-states.edit', $state) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:pen-line" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-states.destroy', $state) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Supprimer cet état ?')">
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
                                <td colspan="6" class="px-4 py-16 text-center text-sm text-zinc-500">Aucun état enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($states->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $states->links() }}</div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
