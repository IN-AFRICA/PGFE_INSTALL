<x-layouts.modules-layout :breadcrumbs="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Comptabilité', 'url' => '#']]">
    <x-backend.pages.module-sidebar-layout 
        logoIcon="lucide:banknote"
        :menuItems="[
            ['label' => 'Tableau de bord', 'icon' => 'lucide:layout-grid', 'url' => route('admin.accounting.index'), 'active' => ($section === 'dashboard')],
            ['label' => 'Plan Comptable', 'icon' => 'lucide:book-open', 'url' => route('admin.accounting.index', ['section' => 'account-plans']), 'active' => ($section === 'account-plans')],
            ['label' => 'Saisie Journal', 'icon' => 'lucide:clipboard-list', 'url' => route('admin.accounting.index', ['section' => 'journal']), 'active' => ($section === 'journal')],
            ['label' => 'États Financiers', 'icon' => 'lucide:bar-chart-3', 'url' => route('admin.accounting.index', ['section' => 'reports']), 'active' => ($section === 'reports')],
            ['label' => 'Trésorerie', 'icon' => 'lucide:wallet', 'url' => route('admin.accounting.index', ['section' => 'tresorerie']), 'active' => ($section === 'tresorerie')],
        ]"
    >
        <div class="space-y-8">
            <!-- Premium Header -->
            <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-10 shadow-2xl shadow-orange-500/30 text-white">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[10px] font-black uppercase tracking-widest mb-4 border border-white/20">
                            <iconify-icon icon="lucide:lock-keyhole" class="text-white" width="12"></iconify-icon>
                            Module Sécurisé
                        </div>
                        <h1 class="text-4xl font-black tracking-tight flex items-center gap-4">
                            Comptabilité Digitale
                        </h1>
                        <p class="mt-3 text-orange-100 text-lg font-medium max-w-2xl">
                            Gestion financière centralisée. Pilotez les comptes, suivez les flux de trésorerie et générez les états financiers en temps réel.
                        </p>
                    </div>
                </div>
                <!-- Abstract Shapes -->
                <div class="absolute -right-24 -top-32 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-yellow-400/20 to-transparent blur-[80px]"></div>
                <div class="absolute -left-20 -bottom-32 h-64 w-64 rounded-full bg-rose-600/30 blur-[60px]"></div>
            </div>

            @if(!session('selected_school_id'))
                <div class="rounded-[2.5rem] border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 p-12 text-center">
                    <div class="max-w-md mx-auto flex flex-col items-center">
                        <div class="h-20 w-20 rounded-3xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center mb-6">
                            <iconify-icon icon="mdi:bank-outline" width="40"></iconify-icon>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 dark:text-white mb-2">Sélection Requise</h3>
                        <p class="text-gray-500 dark:text-gray-400 font-medium mb-8">Pour accéder aux livres comptables, veuillez d'abord sélectionner un établissement spécifique.</p>
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-3 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-8 py-4 font-black uppercase text-xs tracking-widest hover:scale-105 transition-transform">
                            CHOISIR UNE ÉCOLE
                            <iconify-icon icon="lucide:arrow-right" width="16"></iconify-icon>
                        </a>
                    </div>
                </div>
            @else
                @if($section === 'dashboard')
                    <!-- Action Grid -->
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
                        <a href="{{ route('admin.accounting.index', ['section' => 'account-plans']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-amber-500/10 transition-all overflow-hidden">
                            <div class="h-14 w-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                <iconify-icon icon="lucide:library" width="28"></iconify-icon>
                            </div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Plan Comptable</h3>
                            <p class="text-sm text-gray-500 font-medium mb-8">Configuration des classes, comptes et sous-comptes selon les normes OHADA.</p>
                            <div class="mt-auto flex items-center text-amber-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                                Gérer les comptes <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                            </div>
                        </a>
                        <a href="{{ route('admin.accounting.index', ['section' => 'journal']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-blue-500/10 transition-all overflow-hidden">
                            <div class="h-14 w-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <iconify-icon icon="lucide:pen-tool" width="28"></iconify-icon>
                            </div>
                            <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Saisie Journal</h3>
                            <p class="text-sm text-gray-500 font-medium mb-8">Enregistrez les opérations courantes : recettes, dépenses et opérations diverses.</p>
                            <div class="mt-auto flex items-center text-blue-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                                Nouvelle Écriture <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                            </div>
                        </a>
                        <!-- ... other cards ... -->
                    </div>
                @else
                    <!-- Section View Container -->
                    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 p-8 min-h-[400px]">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('admin.accounting.index') }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-orange-500 transition-colors">
                                    <iconify-icon icon="lucide:arrow-left" width="20"></iconify-icon>
                                </a>
                                <div>
                                    <h2 class="text-2xl font-black text-gray-800 dark:text-white capitalize">
                                        {{ str_replace('-', ' ', $section) }}
                                    </h2>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Module Comptable OHADA</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <iconify-icon icon="lucide:construction" class="text-gray-200" width="80"></iconify-icon>
                            <p class="mt-4 text-gray-500 font-bold text-lg">Contenu en cours de développement</p>
                            <p class="text-sm text-gray-400">La section {{ $section }} est en cours d'intégration.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </x-backend.pages.module-sidebar-layout>
</x-layouts.modules-layout>
