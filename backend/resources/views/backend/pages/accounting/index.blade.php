<x-layouts.backend-layout :breadcrumbs="[['title' => 'Comptabilité']]">
    <div class="space-y-10">
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
                <div class="flex items-center gap-3">
                    <button disabled class="opacity-75 cursor-not-allowed hidden md:flex flex-col items-end">
                        <span class="text-xs font-bold text-orange-100 uppercase tracking-widest">Exercice</span>
                        <span class="text-2xl font-black">{{ date('Y') }}</span>
                    </button>
                    <div class="h-12 w-px bg-white/20 hidden md:block"></div>
                    <button class="h-14 w-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center hover:bg-white/30 transition-all border border-white/10">
                        <iconify-icon icon="lucide:settings-2" width="24"></iconify-icon>
                    </button>
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
                    <a href="{{ route('admin.schools.index') }}" class="inline-flex items-center gap-3 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-8 py-4 font-black uppercase text-xs tracking-widest hover:scale-105 transition-transform">
                        CHOISIR UNE ÉCOLE
                        <iconify-icon icon="lucide:arrow-right" width="16"></iconify-icon>
                    </a>
                </div>
            </div>
        @else
            @if($section === 'dashboard')
                <!-- Action Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Card: Plan Comptable -->
                    <a href="{{ route('admin.accounting.index', ['section' => 'account-plans']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-amber-500/10 transition-all overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity transform group-hover:scale-110 duration-500">
                        <iconify-icon icon="lucide:book-open" width="120"></iconify-icon>
                    </div>
                    <div class="h-14 w-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:library" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Plan Comptable</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Configuration des classes, comptes et sous-comptes selon les normes OHADA.</p>
                    <div class="mt-auto flex items-center text-amber-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                        Gérer les comptes <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>

                <!-- Card: Journal -->
                <a href="{{ route('admin.accounting.index', ['section' => 'journal']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-blue-500/10 transition-all overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity transform group-hover:scale-110 duration-500">
                        <iconify-icon icon="lucide:clipboard-list" width="120"></iconify-icon>
                    </div>
                    <div class="h-14 w-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:pen-tool" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Saisie Journal</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Enregistrez les opérations courantes : recettes, dépenses et opérations diverses.</p>
                    <div class="mt-auto flex items-center text-blue-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                        Nouvelle Écriture <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>

                <!-- Card: Reports -->
                <a href="{{ route('admin.accounting.index', ['section' => 'reports']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity transform group-hover:scale-110 duration-500">
                        <iconify-icon icon="lucide:pie-chart" width="120"></iconify-icon>
                    </div>
                    <div class="h-14 w-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:bar-chart-3" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">États Financiers</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Visualisez le Grand Livre, la Balance et le Bilan en temps réel.</p>
                    <div class="mt-auto flex items-center text-emerald-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                        Consulter <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>

                <!-- Card: Tresorerie -->
                <a href="{{ route('admin.accounting.index', ['section' => 'tresorerie']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-purple-500/10 transition-all overflow-hidden">
                    <div class="h-14 w-14 rounded-2xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:wallet" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Trésorerie</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Suivi des caisses, banques et rapprochements bancaires.</p>
                     <div class="mt-auto flex items-center text-purple-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                        Voir les soldes <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>

                <!-- Card: Budget -->
                <a href="{{ route('admin.accounting.index', ['section' => 'budget']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-rose-500/10 transition-all overflow-hidden">
                    <div class="h-14 w-14 rounded-2xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 flex items-center justify-center mb-6 shadow-sm group-hover:bg-rose-600 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:target" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Budget Prévisionnel</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Élaboration et suivi de l'exécution budgétaire annuelle.</p>
                     <div class="mt-auto flex items-center text-rose-600 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                        Planifier <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>

                <!-- Card: Settings -->
                <a href="{{ route('admin.accounting.index', ['section' => 'settings']) }}" class="group relative flex flex-col p-8 rounded-[2rem] bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-gray-500/10 transition-all overflow-hidden border-dashed">
                    <div class="h-14 w-14 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 flex items-center justify-center mb-6 group-hover:bg-gray-800 group-hover:text-white transition-colors">
                        <iconify-icon icon="lucide:sliders" width="28"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">Configuration</h3>
                    <p class="text-sm text-gray-500 font-medium mb-8">Paramètres des exercices, devises et préférences comptables.</p>
                     <div class="mt-auto flex items-center text-gray-400 font-black text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform group-hover:text-gray-800 dark:group-hover:text-white">
                        Configurer <iconify-icon icon="lucide:arrow-right" class="ml-2" width="16"></iconify-icon>
                    </div>
                </a>
                </div>
            @else
                <!-- Section View Container -->
                <div class="space-y-8">
                    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 p-8">
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
                            <button class="px-6 py-3 rounded-xl bg-orange-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-orange-600/20 hover:scale-105 transition-all">
                                Action Rapide
                            </button>
                        </div>

                        @if($section === 'account-plans' && isset($accountPlans))
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Code</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Libellé</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Classe</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($accountPlans as $plan)
                                            <tr class="group hover:bg-orange-50/10 transition-colors">
                                                <td class="px-6 py-4 font-black text-orange-600">{{ $plan->code }}</td>
                                                <td class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300">{{ $plan->name }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-black text-gray-500 uppercase">
                                                        {{ $plan->classComptability->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button class="h-8 w-8 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-orange-500 transition-colors">
                                                        <iconify-icon icon="lucide:more-horizontal"></iconify-icon>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-4">{{ $accountPlans->links() }}</div>
                            </div>
                        @elseif($section === 'journal' && isset($journals))
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Compte</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($journals as $log)
                                            <tr class="group hover:bg-blue-50/10 transition-colors">
                                                <td class="px-6 py-4 text-xs font-black text-gray-400">{{ $log->date }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-gray-700 dark:text-gray-300 level-tight">{{ $log->description }}</span>
                                                        @if($log->abandoned)
                                                            <span class="text-[8px] font-black text-rose-500 uppercase tracking-tighter">Annulé</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-xs font-black text-blue-600">{{ $log->accountPlan->code ?? 'N/A' }}</span>
                                                        <span class="text-[10px] font-bold text-gray-400">{{ $log->accountPlan->name ?? '' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-right font-black text-gray-800 dark:text-white">
                                                    {{ number_format($log->montant, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-4">{{ $journals->links() }}</div>
                            </div>
                        @elseif($section === 'payments' && isset($payments))
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Élève</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Frais</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Montant</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($payments as $pay)
                                            <tr class="group hover:bg-emerald-50/10 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-gray-800 dark:text-white">{{ $pay->student->first_name ?? 'N/A' }} {{ $pay->student->last_name ?? '' }}</span>
                                                        <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $pay->student->matricule ?? 'SANS MATRICULE' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-xs font-bold text-gray-500">{{ $pay->fee->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 font-black text-emerald-600">{{ number_format($pay->amount, 2) }} {{ $pay->currency ?? 'USD' }}</td>
                                                <td class="px-6 py-4 text-xs text-gray-400 font-bold">{{ $pay->created_at->format('d/m/Y') }}</td>
                                                <td class="px-6 py-4 text-right">
                                                    <button class="h-8 w-8 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-emerald-500 transition-colors">
                                                        <iconify-icon icon="lucide:printer"></iconify-icon>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-4">{{ $payments->links() }}</div>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-20 border-2 border-dashed border-gray-50 dark:border-gray-800 rounded-[2rem]">
                                <div class="h-20 w-20 rounded-3xl bg-gray-50 dark:bg-gray-800 text-gray-200 flex items-center justify-center mb-6">
                                    <iconify-icon icon="lucide:construction" width="40"></iconify-icon>
                                </div>
                                <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Module en cours de déploiement</p>
                                <p class="text-gray-500 text-center mt-2 max-w-sm">La section <b>{{ $section }}</b> est en cours de migration vers le nouveau moteur comptable.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.backend-layout>
