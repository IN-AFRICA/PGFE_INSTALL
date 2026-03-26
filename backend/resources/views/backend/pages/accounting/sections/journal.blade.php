<div class="admin-data-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-50/80">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Date</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Description</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Montant</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Compte</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($journals as $j)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="whitespace-nowrap px-4 py-3 text-zinc-600">
                            {{ $j->date ? \Illuminate\Support\Carbon::parse($j->date)->format('d/m/Y') : '—' }}</td>
                        <td class="max-w-xs px-4 py-3 text-zinc-800">{{ \Illuminate\Support\Str::limit($j->description ?? '—', 80) }}</td>
                        <td class="px-4 py-3 font-medium text-zinc-900">{{ number_format((float) ($j->montant ?? 0), 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $j->accountPlan?->name ?? $j->account?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-zinc-500">Aucune écriture.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($journals->hasPages())
        <div class="border-t border-zinc-100 px-4 py-3">{{ $journals->links() }}</div>
    @endif
</div>
