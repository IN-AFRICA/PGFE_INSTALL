@php
    $currentRoute = request()->route()?->getName();
    $menuItems = [
        ['label' => 'Utilisateurs', 'icon' => 'lucide:users', 'url' => route('admin.users.index'), 'active' => str_contains($currentRoute, 'admin.users')],
        ['label' => 'Rôles & Permissions', 'icon' => 'lucide:shield-check', 'url' => route('admin.roles.index'), 'active' => str_contains($currentRoute, 'admin.roles')],
        ['label' => 'Écoles', 'icon' => 'lucide:school', 'url' => route('admin.schools.index'), 'active' => str_contains($currentRoute, 'admin.schools')],
        ['label' => 'Classes', 'icon' => 'lucide:school-2', 'url' => route('admin.classrooms.index'), 'active' => str_contains($currentRoute, 'admin.classrooms')],
        ['label' => 'Géographie (Pays)', 'icon' => 'lucide:globe', 'url' => route('admin.countries.index'), 'active' => str_contains($currentRoute, 'admin.countries')],
        ['label' => 'Filières', 'icon' => 'lucide:book-open', 'url' => route('admin.filiaires.index'), 'active' => str_contains($currentRoute, 'admin.filiaires')],
    ];
@endphp

<x-backend.pages.module-sidebar-layout logoIcon="lucide:settings" :menuItems="$menuItems">
    <div class="space-y-6">
        {{ $slot }}
    </div>
</x-backend.pages.module-sidebar-layout>
