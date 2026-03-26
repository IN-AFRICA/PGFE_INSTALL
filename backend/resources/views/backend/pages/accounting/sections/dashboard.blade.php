@php
    $stats = $stats ?? [];
@endphp

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <div class="admin-data-card p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Comptes</p>
        <p class="mt-2 text-2xl font-black tracking-tighter text-zinc-900">{{ number_format($stats['total_accounts'] ?? 0) }}</p>
    </div>
    <div class="admin-data-card p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Budgets</p>
        <p class="mt-2 text-2xl font-black tracking-tighter text-zinc-900">{{ number_format($stats['total_budgets'] ?? 0) }}</p>
    </div>
    <div class="admin-data-card p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Paiements (période)</p>
        <p class="mt-2 text-2xl font-black tracking-tighter text-zinc-900">{{ number_format($stats['total_paid'] ?? 0, 0, ',', ' ') }}</p>
    </div>
    <div class="admin-data-card p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Écritures journal</p>
        <p class="mt-2 text-2xl font-black tracking-tighter text-zinc-900">{{ number_format($journalCount ?? 0) }}</p>
    </div>
</div>

<div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    <a href="{{ route('admin.accounting.index', ['section' => 'account-plans']) }}"
        class="admin-data-card group block p-6 transition-all hover:border-zinc-900 hover:shadow-lg">
        <div
            class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900 transition-colors group-hover:bg-zinc-900 group-hover:text-white">
            <iconify-icon icon="lucide:list-ordered" width="22"></iconify-icon>
        </div>
        <h3 class="text-base font-bold text-zinc-900">Plan comptable</h3>
        <p class="mt-1 text-xs text-zinc-500">Classes et comptes OHADA.</p>
    </a>
    <a href="{{ route('admin.accounting.index', ['section' => 'journal']) }}"
        class="admin-data-card group block p-6 transition-all hover:border-zinc-900 hover:shadow-lg">
        <div
            class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900 transition-colors group-hover:bg-zinc-900 group-hover:text-white">
            <iconify-icon icon="lucide:file-edit" width="22"></iconify-icon>
        </div>
        <h3 class="text-base font-bold text-zinc-900">Journal</h3>
        <p class="mt-1 text-xs text-zinc-500">Écritures et mouvements.</p>
    </a>
    <a href="{{ route('admin.accounting.index', ['section' => 'reports']) }}"
        class="admin-data-card group block p-6 transition-all hover:border-zinc-900 hover:shadow-lg">
        <div
            class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900 transition-colors group-hover:bg-zinc-900 group-hover:text-white">
            <iconify-icon icon="lucide:bar-chart-big" width="22"></iconify-icon>
        </div>
        <h3 class="text-base font-bold text-zinc-900">États & rapports</h3>
        <p class="mt-1 text-xs text-zinc-500">Balances et synthèses.</p>
    </a>
</div>
