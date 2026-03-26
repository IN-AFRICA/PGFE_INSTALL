@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="infra" title="Signalements & états" subtitle="Évolution de l'état physique des ouvrages."
        icon="lucide:activity" breadcrumb-current="Signalements">
        <x-slot name="actions">
            <a href="{{ route('admin.infra-etats.create') }}" class="admin-btn-primary">
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
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Ouvrage</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">État</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($etats as $etat)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ $etat->id }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-zinc-900">{{ $etat->infrastructure->name ?? '—' }}</span>
                                    <span
                                        class="mt-0.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $etat->infrastructure->code ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-zinc-800">{{ $etat->name }}</span>
                                    @if ($etat->description)
                                        <span
                                            class="mt-0.5 block text-xs text-zinc-500">{{ \Illuminate\Support\Str::limit($etat->description, 60) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.infra-etats.edit', $etat) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:pen-line" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.infra-etats.destroy', $etat) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Supprimer cet état ?')">
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
                                <td colspan="4" class="px-4 py-16 text-center text-sm text-zinc-500">Aucun signalement.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($etats->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $etats->links() }}</div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
