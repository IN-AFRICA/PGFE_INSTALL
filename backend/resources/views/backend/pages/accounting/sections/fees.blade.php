<div class="admin-data-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-50/80">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Montant</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Type</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Devise</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">École</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Effet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($fees as $fee)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-4 py-3 font-semibold text-zinc-900">{{ number_format((float) $fee->amount, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $fee->feeType->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $fee->currency->code ?? $fee->currency->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $fee->school->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ optional($fee->effective_date)->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-zinc-500">Aucun frais.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($fees->hasPages())
        <div class="border-t border-zinc-100 px-4 py-3">{{ $fees->links() }}</div>
    @endif
</div>
