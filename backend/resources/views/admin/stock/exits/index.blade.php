@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Sorties de stock" subtitle="Consommations et distributions." icon="lucide:arrow-up-circle"
        breadcrumb-current="Sorties">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-exits.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                Nouvelle sortie
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
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Date / motif</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($exits as $exit)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ $exit->id }}</td>
                                <td class="px-4 py-3 font-medium text-zinc-900">{{ $exit->article->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-rose-700">-{{ $exit->quantity }}</td>
                                <td class="px-4 py-3 text-zinc-600">
                                    <div class="flex flex-col gap-0.5">
                                        <span>{{ $exit->exit_date ? \Illuminate\Support\Carbon::parse($exit->exit_date)->format('d/m/Y') : '—' }}</span>
                                        <span class="text-xs text-zinc-400">{{ $exit->reason ? \Illuminate\Support\Str::limit($exit->reason, 48) : '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.stock-exits.show', $exit) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:eye" width="16"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.stock-exits.edit', $exit) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:pen-line" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-exits.destroy', $exit) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Supprimer cette sortie ? Le stock sera réintégré.')">
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
                                <td colspan="5" class="px-4 py-16 text-center text-sm text-zinc-500">Aucune sortie.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($exits->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $exits->links() }}</div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
