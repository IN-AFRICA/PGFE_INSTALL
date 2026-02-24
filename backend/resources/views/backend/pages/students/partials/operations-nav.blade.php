@php
    $currentRoute = request()->route()?->getName();
    $tabs = [
        ['label' => 'Inscriptions', 'url' => route('admin.registrations.index'), 'active' => str_contains($currentRoute, 'admin.registrations')],
        ['label' => 'Présences', 'url' => route('admin.presences.index'), 'active' => str_contains($currentRoute, 'admin.presences')],
        ['label' => 'Fiche de cotation', 'url' => route('admin.fiche-cotations.index'), 'active' => str_contains($currentRoute, 'admin.fiche-cotations')],
        ['label' => 'Délibération', 'url' => route('admin.deliberations.index'), 'active' => str_contains($currentRoute, 'admin.deliberations')],
        ['label' => 'Repêchage', 'url' => '#', 'active' => false],
        ['label' => 'Validation laureats', 'url' => '#', 'active' => false],
        ['label' => 'Visite de classe', 'url' => route('admin.visits.index'), 'active' => str_contains($currentRoute, 'admin.visits')],
        ['label' => 'Gestion disciplinaire', 'url' => '#', 'active' => false],
        ['label' => 'Bulletin', 'url' => '#', 'active' => false],
    ];
@endphp

<div class="mb-8">
    {{-- Breadcrumbs matches Image 1 --}}
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 mb-6 bg-white dark:bg-gray-900 px-6 py-3 rounded-2xl border border-gray-50 shadow-sm inline-flex">
        <iconify-icon icon="lucide:home" width="14" class="text-gray-300"></iconify-icon>
        <span>Élèves</span>
        <iconify-icon icon="lucide:chevron-right" width="12" class="text-gray-200"></iconify-icon>
        <span>Opérations</span>
        <iconify-icon icon="lucide:chevron-right" width="12" class="text-gray-200"></iconify-icon>
        <span class="text-[#0ea5e9]">Liste</span>
    </div>

    <h2 class="text-xl font-black text-gray-800 dark:text-white mb-6 pl-2 italic">Enseignement formel</h2>

    <div class="flex flex-wrap items-center gap-2 pl-2">
        @foreach($tabs as $tab)
            <a href="{{ $tab['url'] }}" 
               class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all border shadow-sm
               {{ $tab['active'] 
                  ? 'bg-[#0ea5e9] text-white border-transparent shadow-sky-500/20' 
                  : 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-gray-700 hover:bg-gray-50' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
