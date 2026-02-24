@php
    $currentRoute = request()->route()?->getName();

    $menuItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'lucide:layout-grid',
            'route' => route('admin.students.index'),
            'active' => str_starts_with($currentRoute, 'admin.students.'),
        ],
        [
            'label' => 'Saisie préalable',
            'icon' => 'lucide:file-text',
            'route' => '#', 
            'active' => false,
        ],
        [
            'label' => 'Saisie des opérations',
            'icon' => 'lucide:clipboard-list',
            'route' => route('admin.registrations.index'),
            'active' => str_starts_with($currentRoute, 'admin.registrations.')
                || str_starts_with($currentRoute, 'admin.presences.')
                || str_starts_with($currentRoute, 'admin.fiche-cotations.')
                || str_starts_with($currentRoute, 'admin.deliberations.')
                || str_starts_with($currentRoute, 'admin.student-exits.')
                || str_starts_with($currentRoute, 'admin.visits.'),
        ],
    ];
@endphp

@php
    $currentRoute = request()->route()?->getName();

    $menuItems = [
        [
            'label' => 'Tableau de bord',
            'icon' => 'lucide:layout-grid',
            'route' => route('admin.students.index'),
            'active' => str_starts_with($currentRoute, 'admin.students.'),
        ],
        [
            'label' => 'Saisie préalable',
            'icon' => 'lucide:file-text',
            'route' => '#', 
            'active' => false,
        ],
        [
            'label' => 'Saisie des opérations',
            'icon' => 'lucide:clipboard-list',
            'route' => route('admin.registrations.index'),
            'active' => str_starts_with($currentRoute, 'admin.registrations.')
                || str_starts_with($currentRoute, 'admin.presences.')
                || str_starts_with($currentRoute, 'admin.fiche-cotations.')
                || str_starts_with($currentRoute, 'admin.deliberations.')
                || str_starts_with($currentRoute, 'admin.student-exits.')
                || str_starts_with($currentRoute, 'admin.visits.'),
        ],
    ];
@endphp

<div class="flex flex-col lg:flex-row lg:items-start grow gap-10">
    <aside class="w-full lg:w-64 flex-shrink-0 flex flex-col min-h-[calc(100vh-8rem)]">
        <!-- Logo Area -->
        <div class="mb-10 pl-2">
            <div class="h-16 w-16">
                <!-- Simulation d'un logo type PGFE -->
                <div class="h-full w-full rounded-2xl bg-white shadow-sm flex items-center justify-center p-2 border border-gray-50">
                    <iconify-icon icon="lucide:graduation-cap" class="text-blue-600" width="32"></iconify-icon>
                </div>
            </div>
        </div>

        <!-- Retour Button -->
        <div class="mb-10">
            <button type="button" onclick="window.location.href='{{ route('admin.dashboard') }}'" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white border border-gray-100 shadow-sm text-xs font-black text-gray-500 hover:text-blue-600 hover:shadow-md transition-all group">
                <iconify-icon icon="lucide:arrow-left" width="18" class="text-gray-400 group-hover:text-blue-500"></iconify-icon>
                <span>Retour</span>
            </button>
        </div>

        <!-- Main Menu -->
        <nav class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col gap-2">
            @foreach ($menuItems as $item)
                @php $active = $item['active']; @endphp
                <a
                    href="{{ $item['route'] }}"
                    class="flex items-center gap-4 px-6 py-5 rounded-[1.8rem] text-[11px] font-black uppercase tracking-wider transition-all
                        {{ $active
                            ? 'bg-[#0ea5e9] text-white shadow-lg shadow-sky-500/30'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <div class="flex items-center justify-center w-6 h-6">
                        <iconify-icon icon="{{ $item['icon'] }}" width="22"></iconify-icon>
                    </div>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <!-- Logout Area (Stick to bottom) -->
        <div class="mt-auto pt-16">
            <form method="POST" action="{{ route('web.auth.logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-4 px-8 py-4 rounded-2xl bg-white border border-gray-100 shadow-sm text-xs font-black text-gray-600 hover:text-red-600 hover:shadow-md transition-all group">
                    <iconify-icon icon="lucide:arrow-left" width="20" class="text-gray-400 group-hover:text-red-500"></iconify-icon>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 min-w-0">
        {{ $slot }}
    </main>
</div>
