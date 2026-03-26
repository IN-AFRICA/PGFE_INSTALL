@extends('backend.layouts.app')

@section('admin-content')
    <div class="max-w-7xl mx-auto py-10 px-6 space-y-10">

        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 pb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Tableau de bord</h1>
                <p class="text-zinc-500 text-sm mt-1">Gérez vos établissements, effectifs et flux financiers depuis une interface centralisée.</p>
            </div>

            @if($selected_school_id)
                <div class="flex items-center gap-3 bg-zinc-50 border border-zinc-200 p-1 pr-4 rounded-md shadow-sm">
                    <div class="bg-zinc-900 text-white p-2 rounded-[4px]">
                        <iconify-icon icon="lucide:school" class="text-lg"></iconify-icon>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 leading-none">École active</span>
                        <span class="text-sm font-semibold text-zinc-900">{{ $schools->firstWhere('id', $selected_school_id)?->name }}</span>
                    </div>
                    <a href="{{ route('admin.dashboard', ['school_id' => '']) }}" class="ml-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                        <iconify-icon icon="lucide:x-circle"></iconify-icon>
                    </a>
                </div>
            @endif
        </header>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => 'Total Écoles', 'value' => $count_schools, 'icon' => 'lucide:network', 'sub' => 'Réseau global'],
                ['label' => 'Élèves inscrits', 'value' => $count_students, 'icon' => 'lucide:users', 'sub' => '+2.5% ce mois'],
                ['label' => 'Classes actives', 'value' => $count_classrooms, 'icon' => 'lucide:layout', 'sub' => 'Taux d\'occupation 94%'],
                ['label' => 'Personnel RH', 'value' => $count_personnels, 'icon' => 'lucide:contact-2', 'sub' => 'Contrats actifs'],
            ] as $stat)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-zinc-900">{{ $stat['label'] }}</h3>
                        <iconify-icon icon="{{ $stat['icon'] }}" class="text-zinc-400 text-lg"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-zinc-900">{{ number_format($stat['value'], 0, ',', ' ') }}</div>
                        <p class="text-xs text-zinc-500 mt-1">{{ $stat['sub'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight text-zinc-900 px-1">Modules de gestion</h2>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @php
                    $modules = [
                        ['n' => 'Ressources Humaines', 'i' => 'lucide:user-cog', 'r' => 'admin.personnels.index', 'd' => 'Gestion des agents, paie et contrats.'],
                        ['n' => 'Gestion Élèves', 'i' => 'lucide:graduation-cap', 'r' => 'admin.students.index', 'd' => 'Inscriptions, dossiers et suivi académique.'],
                        ['n' => 'Horaires & Planning', 'i' => 'lucide:calendar', 'r' => 'admin.planning.index', 'd' => 'Emplois du temps et salles de classe.'],
                        ['n' => 'Stocks & Matériel', 'i' => 'lucide:package', 'r' => 'admin.stock-articles.index', 'd' => 'Inventaire, consommables et logistique.'],
                        ['n' => 'Infrastructures', 'i' => 'lucide:building', 'r' => 'admin.infra.dashboard', 'd' => 'Maintenance des bâtiments et équipements.'],
                        ['n' => 'Insertion Pro', 'i' => 'lucide:briefcase', 'r' => 'admin.activities.index', 'd' => 'Stages, alternance et suivi carrières.'],
                        ['n' => 'Synchronisation', 'i' => 'lucide:refresh-cw', 'r' => 'admin.sync.monitoring', 'd' => 'Monitoring des flux de données distants.'],
                        ['n' => 'Administration', 'i' => 'lucide:shield-check', 'r' => 'admin.users.index', 'd' => 'Rôles, permissions et sécurité système.'],
                    ];
                @endphp

                @foreach($modules as $module)
                    <a href="{{ route($module['r'], array_filter(['school_id' => $selected_school_id])) }}"
                       class="group relative flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm hover:bg-zinc-50 hover:border-zinc-300 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 group-hover:bg-white transition-colors">
                                <iconify-icon icon="{{ $module['i'] }}" class="text-lg text-zinc-600 group-hover:text-zinc-900"></iconify-icon>
                            </div>
                            <h3 class="font-bold text-zinc-900">{{ $module['n'] }}</h3>
                        </div>
                        <p class="text-sm text-zinc-500 leading-relaxed">{{ $module['d'] }}</p>
                    </a>
                @endforeach

                <div class="md:col-span-2 lg:col-span-1 rounded-xl border border-zinc-900 bg-zinc-900 p-6 shadow-lg flex flex-col justify-between">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <div class="bg-zinc-800 p-2 rounded-lg">
                                <iconify-icon icon="lucide:banknote" class="text-xl"></iconify-icon>
                            </div>
                            <h3 class="font-bold tracking-tight">Comptabilité</h3>
                        </div>
                        <iconify-icon icon="lucide:lock" class="text-zinc-500 {{ $selected_school_id ? 'hidden' : '' }}"></iconify-icon>
                    </div>

                    <p class="text-zinc-400 text-sm mt-4 leading-relaxed">
                        Gestion des frais de scolarité, grand livre et rapports financiers.
                    </p>

                    <a href="{{ route('admin.accounting.index') }}"
                       class="mt-6 inline-flex h-9 items-center justify-center rounded-md bg-white px-4 text-sm font-medium text-zinc-900 hover:bg-zinc-200 transition-colors">
                        Accéder au module
                    </a>
                </div>
            </div>
        </div>

        <footer class="mt-12 pt-6 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">
            {{ config('app.name') }}
        </span>
        <span class="h-1 w-1 rounded-full bg-zinc-200"></span>
        <span class="text-[10px] font-medium text-zinc-300 uppercase tracking-widest italic">
            Powered by INAFRICA
        </span>
    </div>

    <div class="flex items-center gap-6">
        <div class="flex items-center gap-4 border-r border-zinc-100 pr-6">
            <a href="https://github.com/IN-AFRICA" target="_blank" class="text-zinc-400 hover:text-zinc-900 transition-colors">
                <iconify-icon icon="lucide:github" width="16"></iconify-icon>
            </a>
            <a href="#" class="text-zinc-400 hover:text-zinc-900 transition-colors" title="Centre d'aide">
                <iconify-icon icon="lucide:help-circle" width="16"></iconify-icon>
            </a>
        </div>
        
        <div class="flex items-center gap-1.5">
            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500/80"></div>
            <span class="text-[9px] font-bold text-zinc-400 uppercase tracking-tighter italic">Progiciel</span>
        </div>
    </div>
</footer>
    </div>
@endsection
