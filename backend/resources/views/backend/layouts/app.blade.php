<!doctype html>
<html lang="fr" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e4e4e7; border-radius: 10px; }
    </style>
</head>

<body class="h-full bg-zinc-50 text-zinc-950 antialiased">

@php
    $currentRoute = request()->route()?->getName() ?? '';
    
    // Définition des modules qui activent la navigation latérale
    $modules = [
    'infra', 'stock', 'mois', 'schools', 'countries', 'provinces', 'communes', 'territories',
    'classrooms', 'students', 'presences', 'fiche-cotations', 'deliberations', 'student-exits', 'visits',
    'personnels', 'personnel-presences', 'person-affectations', 'filiaires', 'types', 'academic-levels', 'planning', 'activities', 'users', 'roles',
    'registrations', 'accounting', 'school-years', 'semesters', 'periods'
];
    $showSidebar = false;

    foreach ($modules as $module) {
        if (str_starts_with($currentRoute, $module) || str_contains($currentRoute, $module)) {
            $showSidebar = true;
            break;
        }
    }
    
    // La Navbar est généralement toujours visible en Admin, 
    // mais on garde ta logique si tu veux l'isoler.
    $showNavbar = $showSidebar || str_contains($currentRoute, 'admin'); 
@endphp



<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    @if($showSidebar)
    <aside class="fixed left-0 z-30 hidden h-[calc(100vh-3.5rem)] w-64 flex-col lg:flex">
        <div class="flex min-h-0 flex-1 flex-col">
            @includeIf('backend.layouts.partials.sidebar.menu')
        </div>
    </aside>
    @endif

    {{-- MAIN AREA --}}
    <main class="flex-1 {{ $showSidebar ? 'lg:pl-64' : '' }}">
        <div class="admin-app-shell mx-auto flex min-h-full max-w-[1400px] flex-col px-5 py-8 sm:px-6 lg:px-10 lg:py-10">
            
            {{-- MESSAGES & NOTIFS --}}
            <div class="space-y-3 mb-8">
                @if(session('success'))
                    <div class="flex items-center gap-3 rounded-md border border-emerald-200 bg-emerald-50/50 px-4 py-3 text-xs font-semibold text-emerald-900 animate-in fade-in slide-in-from-top-1">
                        <iconify-icon icon="lucide:check-circle" class="text-emerald-500 text-base"></iconify-icon>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50/50 px-4 py-3 text-xs text-red-900 animate-in fade-in slide-in-from-top-1">
                        <div class="flex items-center gap-3 font-bold mb-1">
                            <iconify-icon icon="lucide:octagon-alert" class="text-red-500 text-base"></iconify-icon>
                            Erreur détectée
                        </div>
                        <ul class="pl-7 list-disc opacity-80 font-medium">
                            @if(session('error')) <li>{{ session('error') }}</li> @endif
                            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- CONTENT --}}
            <div class="flex-1 animate-in fade-in duration-700">
                @hasSection('admin-content')
                    @yield('admin-content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </div>

            {{-- FOOTER --}}
            
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>