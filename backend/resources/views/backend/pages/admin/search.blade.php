<x-layouts.backend-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Recherche Multi-Écoles', 'url' => '#']
]">
    <div class="space-y-10">
        <!-- Floating Modern Header -->
        <div class="relative overflow-hidden rounded-[2.5rem] bg-white dark:bg-gray-900 p-10 shadow-2xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div class="relative z-10">
                <h1 class="text-3xl font-black text-gray-800 dark:text-white flex items-center gap-3">
                    <div class="h-12 w-12 rounded-2xl bg-violet-600 flex items-center justify-center text-white shadow-lg shadow-violet-600/20">
                        <iconify-icon icon="lucide:globe-2" width="28"></iconify-icon>
                    </div>
                    Radar de Recherche Global
                </h1>
                <p class="mt-2 text-gray-500 font-medium max-w-2xl">Scannez l'intégralité du réseau national pour localiser instantanément des élèves ou des agents administratifs.</p>
                
                <form action="{{ route('admin.search') }}" method="GET" class="mt-8 flex gap-3">
                    <div class="relative flex-1 group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-gray-400 group-focus-within:text-violet-600 transition-colors">
                            <iconify-icon icon="lucide:search" width="24"></iconify-icon>
                        </span>
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $q }}" 
                            placeholder="Rechercher par nom, prénom, matricule ou ville..." 
                            class="w-full h-16 pl-14 pr-6 rounded-[1.5rem] border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 dark:text-gray-100 focus:border-violet-600 focus:bg-white dark:focus:bg-gray-900 focus:ring-4 focus:ring-violet-500/10 transition-all text-lg font-medium"
                        >
                    </div>
                    <button type="submit" class="h-16 px-10 rounded-[1.5rem] bg-violet-600 text-white font-black text-lg hover:bg-violet-700 shadow-xl shadow-violet-600/20 active:scale-95 transition-all">
                        LANCER LE SCAN
                    </button>
                </form>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-violet-50 dark:bg-violet-900/10 blur-3xl opacity-50"></div>
        </div>

        @if($q)
            <div class="grid gap-10 lg:grid-cols-2">
                <!-- Résultats Élèves -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            <iconify-icon icon="lucide:users" class="text-violet-600" width="18"></iconify-icon>
                            Élèves détectés ({{ $students->total() }})
                        </h3>
                    </div>
                    <div class="grid gap-4">
                        @forelse($students as $student)
                            <div class="group relative bg-white dark:bg-gray-900 p-5 rounded-[2rem] border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-violet-500/10 transition-all flex items-center justify-between overflow-hidden">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-inner">
                                        {{ mb_substr($student->firstname ?? '?', 0, 1) }}{{ mb_substr($student->lastname ?? '', 0, 1) }}
                                    </div>
                                    <div class="truncate">
                                        <p class="font-black text-gray-800 dark:text-white group-hover:text-violet-600 transition-colors uppercase tracking-tight">{{ $student->firstname }} {{ $student->lastname }} {{ $student->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full font-black text-gray-400">MAT: {{ $student->matricule ?? 'N/A' }}</span>
                                            <span class="text-[10px] font-bold text-violet-600 uppercase">{{ $student->school->name ?? 'École inconnue' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.dashboard', ['school_id' => $student->school_id]) }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-violet-600 hover:text-white transition-all transform group-hover:scale-110">
                                    <iconify-icon icon="lucide:arrow-right" width="20"></iconify-icon>
                                </a>
                            </div>
                        @empty
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-20 rounded-[2.5rem] border-2 border-dashed border-gray-100 dark:border-gray-800 text-center">
                                <iconify-icon icon="lucide:ghost" class="text-gray-200" width="64"></iconify-icon>
                                <p class="text-gray-400 font-bold mt-4">Aucun enregistrement trouvé</p>
                            </div>
                        @endforelse
                    </div>
                    @if($students->hasPages())
                        <div class="px-4">
                            {{ $students->onEachSide(1)->appends(['q' => $q])->links() }}
                        </div>
                    @endif
                </div>

                <!-- Résultats Personnels -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            <iconify-icon icon="lucide:id-card" class="text-emerald-500" width="18"></iconify-icon>
                            Cadre Académique ({{ $personals->total() }})
                        </h3>
                    </div>
                    <div class="grid gap-4">
                        @forelse($personals as $p)
                            <div class="group relative bg-white dark:bg-gray-900 p-5 rounded-[2rem] border border-gray-100 dark:border-gray-800 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all flex items-center justify-between overflow-hidden">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white font-black text-lg">
                                        {{ mb_substr($p->name ?? '?', 0, 2) }}
                                    </div>
                                    <div class="truncate">
                                        <p class="font-black text-gray-800 dark:text-white group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ $p->pre_name }} {{ $p->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full font-black text-gray-400">ID: {{ $p->id }}</span>
                                            <span class="text-[10px] font-bold text-emerald-600 uppercase">{{ $p->school->name ?? 'National' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.dashboard', ['school_id' => $p->school_id]) }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-400 hover:bg-emerald-600 hover:text-white transition-all transform group-hover:scale-110">
                                    <iconify-icon icon="lucide:arrow-right" width="20"></iconify-icon>
                                </a>
                            </div>
                        @empty
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-20 rounded-[2.5rem] border-2 border-dashed border-gray-100 dark:border-gray-800 text-center">
                                <iconify-icon icon="lucide:id-card" class="text-gray-200" width="64"></iconify-icon>
                                <p class="text-gray-400 font-bold mt-4">Aucun agent détecté</p>
                            </div>
                        @endforelse
                    </div>
                    @if($personals->hasPages())
                        <div class="px-4">
                            {{ $personals->onEachSide(1)->appends(['q' => $q])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Placeholder for empty scan -->
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="relative mb-8">
                    <div class="absolute inset-0 rounded-full bg-violet-500 animate-ping opacity-20"></div>
                    <div class="h-24 w-24 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-300 dark:text-gray-700 relative border-2 border-gray-100 dark:border-gray-800">
                        <iconify-icon icon="lucide:send" width="48"></iconify-icon>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white">Prêt à scanner ?</h3>
                <p class="text-gray-500 font-medium max-w-sm mt-2">Saisissez un mot-clé ci-dessus pour rechercher au-delà des frontières d'un seul établissement.</p>
            </div>
        @endif
    </div>
</x-layouts.backend-layout>
