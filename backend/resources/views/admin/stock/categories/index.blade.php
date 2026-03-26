@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Catégories" subtitle="Classification des articles en stock." icon="lucide:tags"
        breadcrumb-current="Catégories">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-categories.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                Nouvelle catégorie
            </a>
        </x-slot>

        <div class="admin-data-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Réf.</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Intitulé</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($categories as $cat)
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ str_pad((string) $cat->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3 font-semibold text-zinc-900">{{ $cat->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.stock-categories.show', $cat) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:eye" width="16"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.stock-categories.edit', $cat) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white">
                                            <iconify-icon icon="lucide:pen-line" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-categories.destroy', $cat) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Supprimer cette catégorie ?')">
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
                                <td colspan="3" class="px-4 py-16 text-center text-sm text-zinc-500">Aucune catégorie.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $categories->links() }}</div>
            @endif
        </div>

        <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50/40 p-5 text-sm text-zinc-600">
            <p><span class="font-semibold text-zinc-900">Astuce :</span> des noms explicites facilitent les filtres et rapports (ex. papeterie, maintenance).</p>
        </div>
    </x-admin.shadcn-shell>
@endsection
