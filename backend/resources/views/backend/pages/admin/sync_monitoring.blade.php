<x-layouts.backend-layout :breadcrumbs="[
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Supervision Temps Réel', 'url' => '#']
]">
    <div class="space-y-10">
        <!-- Floating Header with Status Pulse -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-gray-900 p-8 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 rounded-[1.5rem] bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/30">
                    <iconify-icon icon="lucide:activity" width="36" class="animate-pulse"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">Supervision des Flux</h1>
                    <p class="text-sm text-gray-500 font-medium">Monitoring dynamique de la synchronisation entre les terminaux locaux et le hub central.</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
               <div class="px-4 py-2 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></div>
                    <span class="text-xs font-black uppercase tracking-widest">Canal Actif</span>
               </div>
            </div>
        </div>

        <!-- Monitoring Board -->
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-950/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Source</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Volume de Données</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Dernière Impulsion</th>
                            <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-[0.2em] text-right">État du Noeud</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($schools as $school)
                            @php
                                $statusConfig = match($school['status']) {
                                    'ok' => ['bg' => 'bg-emerald-500', 'light' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300', 'label' => 'OPERATIONNEL'],
                                    'warning' => ['bg' => 'bg-amber-500', 'light' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300', 'label' => 'DELAYED'],
                                    'danger' => ['bg' => 'bg-rose-500', 'light' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300', 'label' => 'CRITICAL'],
                                    default => ['bg' => 'bg-gray-400', 'light' => 'bg-gray-50 text-gray-700', 'label' => 'UNKNOWN']
                                };
                            @endphp
                            <tr class="group hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 font-black text-xs uppercase shadow-sm">
                                            {{ mb_substr($school['name'], 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-white uppercase tracking-tighter">{{ $school['name'] }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                                                <iconify-icon icon="lucide:map-pin" width="10"></iconify-icon>
                                                {{ $school['city'] }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-gray-600 dark:text-gray-300">{{ $school['students_count'] }}</span>
                                            <span class="text-[10px] uppercase font-bold text-gray-400">Élèves</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-emerald-600">{{ $school['personals_count'] }}</span>
                                            <span class="text-[10px] uppercase font-bold text-gray-400">Agents</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-800 dark:text-white italic">
                                            {{ $school['last_sync'] ? \Carbon\Carbon::parse($school['last_sync'])->diffForHumans() : 'SANS DONNÉE' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 mt-0.5">
                                            {{ $school['last_sync'] ? \Carbon\Carbon::parse($school['last_sync'])->format('d/m/Y - H:i') : '--:--' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-end">
                                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black tracking-widest border {{ $statusConfig['light'] }} border-transparent transition-all group-hover:shadow-lg">
                                            <span class="h-2 w-2 rounded-full {{ $statusConfig['bg'] }}"></span>
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-8 bg-gray-50/50 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4 text-xs text-gray-400 font-medium">
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Opérationnel</span>
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Retard (24h+)</span>
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Critique (72h+)</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
