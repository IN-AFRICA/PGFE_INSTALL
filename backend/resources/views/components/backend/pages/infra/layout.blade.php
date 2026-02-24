@php
    $currentRoute = request()->route()?->getName();
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'lucide:layout-dashboard', 'url' => route('admin.infra.dashboard'), 'active' => $currentRoute === 'admin.infra.dashboard'],
        ['label' => 'Catégories', 'icon' => 'lucide:sidebar', 'url' => route('admin.infra-categories.index'), 'active' => str_contains($currentRoute, 'infra-categories')],
        ['label' => 'Bailleurs', 'icon' => 'lucide:users', 'url' => route('admin.infra-bailleurs.index'), 'active' => str_contains($currentRoute, 'infra-bailleurs')],
        ['label' => 'Équipements', 'icon' => 'lucide:monitor', 'url' => route('admin.infra-equipements.index'), 'active' => str_contains($currentRoute, 'infra-equipements')],
        ['label' => 'États', 'icon' => 'lucide:file-text', 'url' => route('admin.infra-etats.index'), 'active' => str_contains($currentRoute, 'infra-etats')],
        ['label' => 'Infrastructures', 'icon' => 'lucide:building-2', 'url' => route('admin.infra-infrastructures.index'), 'active' => str_contains($currentRoute, 'infra-infrastructures')],
        ['label' => 'Inventaires', 'icon' => 'lucide:clipboard-check', 'url' => route('admin.infra-inventaires.index'), 'active' => str_contains($currentRoute, 'infra-inventaires')],
    ];
@endphp

<x-backend.pages.module-sidebar-layout logoIcon="lucide:building-2" :menuItems="$menuItems">
    <div class="space-y-6">
        {{ $slot }}
    </div>
</x-backend.pages.module-sidebar-layout>
