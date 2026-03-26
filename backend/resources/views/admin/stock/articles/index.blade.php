@extends('backend.layouts.app')

@section('admin-content')
    <x-admin.shadcn-shell module="stock" title="Articles" subtitle="Consommables, fournitures et marchandises."
        icon="lucide:package-search" breadcrumb-current="Articles">
        <x-slot name="actions">
            <a href="{{ route('admin.stock-articles.create') }}" class="admin-btn-primary">
                <iconify-icon icon="lucide:plus" width="16"></iconify-icon>
                Nouvel article
            </a>
        </x-slot>

        <div class="admin-data-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">ID</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Article</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Catégorie</th>
                            <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Stock</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($articles as $art)
                            @php $lowStock = $art->quantity <= ($art->min_threshold ?? 5); @endphp
                            <tr class="hover:bg-zinc-50/50">
                                <td class="px-4 py-3 font-mono text-xs text-zinc-500">#{{ $art->id }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-zinc-900">{{ $art->name }}</span>
                                    <span
                                        class="mt-0.5 block text-[10px] font-bold uppercase text-zinc-400">{{ $art->provider->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-zinc-600">{{ $art->category->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-bold {{ $lowStock ? 'text-rose-600' : 'text-zinc-900' }}">{{ $art->quantity }}</span>
                                    <span class="ml-2 text-[10px] text-zinc-400">{{ $lowStock ? 'À réapprovisionner' : 'OK' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.stock-articles.show', $art) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white"
                                            title="Voir">
                                            <iconify-icon icon="lucide:eye" width="16"></iconify-icon>
                                        </a>
                                        <a href="{{ route('admin.stock-articles.edit', $art) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 hover:bg-zinc-900 hover:text-white"
                                            title="Modifier">
                                            <iconify-icon icon="lucide:edit-3" width="16"></iconify-icon>
                                        </a>
                                        <form action="{{ route('admin.stock-articles.destroy', $art) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Supprimer cet article ?')">
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
                                <td colspan="5" class="px-4 py-16 text-center text-sm text-zinc-500">Aucun article.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($articles->hasPages())
                <div class="border-t border-zinc-100 px-4 py-3">{{ $articles->links() }}</div>
            @endif
        </div>
    </x-admin.shadcn-shell>
@endsection
