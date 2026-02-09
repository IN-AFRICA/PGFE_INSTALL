<x-layouts.backend-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    $selected_school_id ? ['label' => $schools->firstWhere('id', $selected_school_id)?->name, 'url' => '#'] : null
]">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 p-10 shadow-2xl shadow-indigo-500/20 mb-10">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="text-white">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Système Live
                </div>
                <h1 class="text-4xl font-black tracking-tight md:text-5xl">
                    Salut, {{ explode(' ', auth()->user()->name)[0] }} !
                </h1>
                <p class="mt-3 text-indigo-100 text-lg font-medium opacity-90 max-w-xl">
                    @if($selected_school_id)
                        Supervision active pour : <span class="font-black text-white px-2 py-1 rounded-lg bg-white/10">{{ $schools->firstWhere('id', $selected_school_id)?->name }}</span>
                    @else
                        Bienvenue dans votre centre de contrôle scolaire. Prêt pour une nouvelle journée de gestion ?
                    @endif
                </p>
            </div>
            @if($selected_school_id)
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard', ['school_id' => '']) }}" class="group inline-flex items-center gap-3 rounded-2xl bg-white px-8 py-4 text-sm font-black text-indigo-600 shadow-xl hover:bg-indigo-50 transition-all active:scale-95">
                        <iconify-icon icon="lucide:arrow-left" width="20" class="transition-transform group-hover:-translate-x-1"></iconify-icon>
                        VUE GLOBALE
                    </a>
                </div>
            @endif
        </div>
        <!-- Abstract Shapes -->
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl"></div>
    </div>

    @if(auth()->user()?->hasRole('super-admin'))
        <!-- Super Admin Quick Controls -->
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="h-1 w-12 rounded-full bg-violet-600"></div>
                <h2 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Outils de Supervision</h2>
            </div>
            <div class="grid gap-6 sm:grid-cols-3">
                <a href="{{ route('admin.search') }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-transparent hover:border-violet-500/30 hover:shadow-2xl hover:shadow-violet-500/10 transition-all">
                    <div class="bg-violet-50 dark:bg-violet-900/20 text-violet-600 p-4 rounded-2xl w-fit mb-6 group-hover:bg-violet-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                        <iconify-icon icon="lucide:search" width="32"></iconify-icon>
                    </div>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white">Recherche Globale</h3>
                    <p class="text-sm text-gray-500 mt-2 font-medium">Localisez n'importe quel profil à travers le réseau complet.</p>
                </a>
                <a href="{{ route('admin.sync.monitoring') }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-transparent hover:border-blue-500/30 hover:shadow-2xl hover:shadow-blue-500/10 transition-all">
                    <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 p-4 rounded-2xl w-fit mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                        <iconify-icon icon="lucide:refresh-cw" width="32"></iconify-icon>
                    </div>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white">Flux Sync</h3>
                    <p class="text-sm text-gray-500 mt-2 font-medium">Monitorage en temps réel de l'état des terminaux distants.</p>
                </a>
                <a href="{{ route('admin.export-roles-pdf') }}" target="_blank" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-transparent hover:border-rose-500/30 hover:shadow-2xl hover:shadow-rose-500/10 transition-all">
                    <div class="bg-rose-50 dark:bg-rose-900/20 text-rose-600 p-4 rounded-2xl w-fit mb-6 group-hover:bg-rose-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                        <iconify-icon icon="lucide:file-text" width="32"></iconify-icon>
                    </div>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white">Audit Accès</h3>
                    <p class="text-sm text-gray-500 mt-2 font-medium">Exportation sécurisée de la matrice des permissions globales.</p>
                </a>
            </div>
        </div>
    @endif

    <div class="space-y-12">
        <!-- Main Stats Panel -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Schools -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-900/20">
                        <iconify-icon icon="mdi:school" width="28"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-black text-violet-500 uppercase tracking-[0.2em]">Écoles</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-gray-800 dark:text-white">{{ $count_schools }}</span>
                </div>
                <a href="{{ route('admin.schools.index') }}" class="mt-6 flex items-center gap-2 text-xs font-black text-violet-600 group-hover:translate-x-1 transition-transform">
                    VOIR LE RÉSEAU <iconify-icon icon="lucide:chevron-right" width="16"></iconify-icon>
                </a>
            </div>

            <!-- Classes -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20">
                        <iconify-icon icon="lucide:layers" width="28"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">Classes</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-gray-800 dark:text-white">{{ $count_classrooms }}</span>
                </div>
                @if($canManageSchoolScopedCheck)
                    <a href="{{ route('admin.classrooms.index', array_filter(['school_id'=>$selected_school_id])) }}" class="mt-6 flex items-center gap-2 text-xs font-black text-indigo-600 group-hover:translate-x-1 transition-transform">
                        DÉTAILS STRUCTURE <iconify-icon icon="lucide:chevron-right" width="16"></iconify-icon>
                    </a>
                @else
                    <span class="mt-6 block text-[10px] font-black text-gray-400 uppercase tracking-widest italic opacity-50">SÉLECTION REQUISE</span>
                @endif
            </div>

            <!-- Students -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-pink-50 text-pink-600 dark:bg-pink-900/20">
                        <iconify-icon icon="lucide:users" width="28"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-black text-pink-500 uppercase tracking-[0.2em]">Élèves</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-gray-800 dark:text-white">{{ $count_students }}</span>
                </div>
                @if($canManageSchoolScopedCheck)
                    <a href="{{ route('admin.students.index', array_filter(['school_id'=>$selected_school_id])) }}" class="mt-6 flex items-center gap-2 text-xs font-black text-pink-600 group-hover:translate-x-1 transition-transform">
                        EFFECTIFS LIVE <iconify-icon icon="lucide:chevron-right" width="16"></iconify-icon>
                    </a>
                @else
                    <span class="mt-6 block text-[10px] font-black text-gray-400 uppercase tracking-widest italic opacity-50">SÉLECTION REQUISE</span>
                @endif
            </div>

            <!-- Staff -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 hover:shadow-xl transition-all">
                <div class="flex items-center justify-between mb-6">
                    <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20">
                        <iconify-icon icon="lucide:id-card" width="28"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.2em]">Agents</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-black text-gray-800 dark:text-white">{{ $count_personnels }}</span>
                </div>
                <a href="{{ route('admin.personnels.index', array_filter(['school_id'=>$selected_school_id])) }}" class="mt-6 flex items-center gap-2 text-xs font-black text-emerald-600 group-hover:translate-x-1 transition-transform">
                    GÉRER LES RH <iconify-icon icon="lucide:chevron-right" width="16"></iconify-icon>
                </a>
            </div>
        </div>

        @if($selected_school_id)
            <!-- Specialized Modules (Specific School Context) -->
            <div class="rounded-[2rem] bg-amber-500 p-8 shadow-2xl shadow-amber-500/30">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6 text-white">
                        <div class="h-16 w-16 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                            <iconify-icon icon="lucide:banknote" width="36"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-80">Gouvernance Financière</p>
                            <h2 class="text-2xl font-black">Accéder à la Comptabilité Digitale</h2>
                        </div>
                    </div>
                    <a href="{{ route('admin.accounting.index') }}" class="inline-flex items-center gap-3 rounded-2xl bg-white px-8 py-4 text-sm font-black text-amber-600 shadow-xl hover:bg-amber-50 transition-all active:scale-95">
                        OUVRIR LE MODULE <iconify-icon icon="lucide:lock-open" width="20"></iconify-icon>
                    </a>
                </div>
            </div>
        @else
           <div class="rounded-[2.5rem] border-4 border-dashed border-gray-100 dark:border-gray-800 p-16 text-center">
                <div class="max-w-md mx-auto">
                    <iconify-icon icon="lucide:layers" class="text-gray-200 mb-6" width="64"></iconify-icon>
                    <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-3 tracking-tight">Prêt à plonger plus loin ?</h3>
                    <p class="text-gray-500 font-medium mb-8">Sélectionnez une école pour débloquer les modules de gestion locale (Mois, Stock, Infrastructures, Comptabilité).</p>
                    <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center gap-2 text-violet-600 font-black uppercase text-xs tracking-[0.2em] border-b-2 border-violet-200 pb-1 hover:border-violet-600 transition-all">
                        COMMENCER L'EXPLORATION <iconify-icon icon="lucide:arrow-right" width="16"></iconify-icon>
                    </a>
                </div>
           </div>
        @endif
    </div>

</x-layouts.backend-layout>

