@php
    $currentRoute = request()->route()?->getName();

    $menuItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'lucide:layout-grid',
            'url' => route('admin.students.index'),
            'active' => str_contains($currentRoute, 'admin.students.index'),
        ],
        [
            'label' => 'Saisie préalable',
            'icon' => 'lucide:file-text',
            'url' => route('admin.classrooms.index'), 
            'active' => str_contains($currentRoute, 'admin.classrooms') || str_contains($currentRoute, 'admin.sections') || str_contains($currentRoute, 'admin.courses'),
        ],
        [
            'label' => 'Saisie des opérations',
            'icon' => 'lucide:clipboard-list',
            'url' => route('admin.registrations.index'),
            'active' => str_contains($currentRoute, 'admin.registrations') 
                || str_contains($currentRoute, 'admin.presences')
                || str_contains($currentRoute, 'admin.fiche-cotations')
                || str_contains($currentRoute, 'admin.deliberations')
                || str_contains($currentRoute, 'admin.student-exits')
                || str_contains($currentRoute, 'admin.visits'),
        ],
    ];
@endphp

<x-backend.pages.module-sidebar-layout 
    logoIcon="lucide:graduation-cap"
    :menuItems="$menuItems"
>
    <div class="space-y-6">
        {{ $slot }}
    </div>
</x-backend.pages.module-sidebar-layout>
