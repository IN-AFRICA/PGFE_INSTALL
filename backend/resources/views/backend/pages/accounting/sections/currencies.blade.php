<div class="admin-data-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-50/80">
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Code</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Nom</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Symbole</th>
                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-zinc-400">Taux actif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($currencies as $c)
                    <tr class="hover:bg-zinc-50/50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-zinc-900">{{ $c->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-700">{{ $c->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-600">{{ $c->symbol ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-600">
                            {{ $c->activeExchangeRate?->rate !== null ? number_format((float) $c->activeExchangeRate->rate, 4, ',', ' ') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center text-sm text-zinc-500">Aucune devise.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($currencies->hasPages())
        <div class="border-t border-zinc-100 px-4 py-3">{{ $currencies->links() }}</div>
    @endif
</div>
