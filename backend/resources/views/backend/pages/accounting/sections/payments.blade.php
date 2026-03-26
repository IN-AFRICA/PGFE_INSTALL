<div class="admin-data-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-50/80">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Réf.</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Élève</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Frais</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Montant</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($payments as $p)
                    @php
                        $stu = $p->student;
                        $stuName = $stu
                            ? trim(implode(' ', array_filter([$stu->firstname ?? '', $stu->lastname ?? '', $stu->name ?? ''])))
                            : '—';
                    @endphp
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-zinc-600">{{ $p->reference ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-zinc-900">{{ $stuName }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $p->fee?->feeType?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-zinc-900">{{ number_format((float) $p->amount, 2, ',', ' ') }}
                            {{ $p->currency?->code ?? '' }}</td>
                        <td class="px-4 py-3">
                            @if ($p->confirmed_at)
                                <span
                                    class="inline-flex rounded-md border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">Confirmé</span>
                            @elseif($p->paid_at)
                                <span
                                    class="inline-flex rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-600">Payé</span>
                            @else
                                <span
                                    class="inline-flex rounded-md border border-amber-100 bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">En cours</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-zinc-500">Aucun paiement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($payments->hasPages())
        <div class="border-t border-zinc-100 px-4 py-3">{{ $payments->links() }}</div>
    @endif
</div>
