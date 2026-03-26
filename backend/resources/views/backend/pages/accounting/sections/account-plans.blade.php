<div class="admin-data-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-50/80">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Code</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Intitulé</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Classe</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Catégorie</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($accountPlans as $row)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-zinc-600">{{ $row->code }}</td>
                        <td class="px-4 py-3 font-medium text-zinc-900">{{ $row->name }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $row->classComptability->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $row->categoryComptability->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-zinc-500">Aucun compte.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($accountPlans->hasPages())
        <div class="border-t border-zinc-100 px-4 py-3">{{ $accountPlans->links() }}</div>
    @endif
</div>
