<x-layouts.modules-layout>
    <x-backend.pages.students.partials.layout>
        <!-- Header Dashboard Élèves -->
        <div class="mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">Bonjour, Bienvenue à nouveau sur votre plateforme.</p>
                </div>
            </div>

            <!-- Global Filters -->
            <div class="flex flex-wrap items-center gap-3 mb-10">
                <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-100 bg-white text-[11px] font-black text-gray-400 shadow-sm hover:bg-gray-50 transition-all uppercase tracking-widest">
                    Filtres <iconify-icon icon="lucide:sliders-horizontal" width="14"></iconify-icon>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0ea5e9] text-white text-[11px] font-black shadow-lg shadow-sky-500/20 uppercase tracking-widest">
                        <span>2025-2026</span>
                        <iconify-icon icon="lucide:x" width="14" class="opacity-80"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Total Card -->
                <div class="relative bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 group hover:shadow-xl transition-all">
                    <div class="h-10 w-10 rounded-full bg-[#14b8a6]/10 text-[#14b8a6] flex items-center justify-center mb-6">
                        <iconify-icon icon="lucide:user" width="20"></iconify-icon>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total</p>
                    <h3 class="text-4xl font-black text-gray-800 dark:text-white mb-6">{{ number_format($stats['total'] ?? 0) }}</h3>
                    <p class="text-[11px] font-bold text-gray-400">Total des apprenants</p>
                    <div class="absolute bottom-10 right-10 h-8 w-8 rounded-full bg-[#14b8a6] text-white flex items-center justify-center shadow-lg shadow-teal-500/30 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform">
                        <iconify-icon icon="lucide:arrow-up-right" width="16"></iconify-icon>
                    </div>
                </div>

                <!-- Boys Card -->
                <div class="relative bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 group hover:shadow-xl transition-all">
                    <div class="h-10 w-10 rounded-full bg-[#0ea5e9]/10 text-[#0ea5e9] flex items-center justify-center mb-6">
                        <iconify-icon icon="lucide:users" width="20"></iconify-icon>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Garçons</p>
                    <h3 class="text-4xl font-black text-gray-800 dark:text-white mb-6">{{ number_format($stats['boys'] ?? 0) }}</h3>
                    <p class="text-[11px] font-bold text-gray-400">Total des garçons</p>
                    <div class="absolute bottom-10 right-10 h-8 w-8 rounded-full bg-[#0ea5e9] text-white flex items-center justify-center shadow-lg shadow-sky-500/30 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform">
                        <iconify-icon icon="lucide:arrow-up-right" width="16"></iconify-icon>
                    </div>
                </div>

                <!-- Girls Card -->
                <div class="relative bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 group hover:shadow-xl transition-all">
                    <div class="h-10 w-10 rounded-full bg-[#eab308]/10 text-[#eab308] flex items-center justify-center mb-6">
                        <iconify-icon icon="lucide:users" width="20"></iconify-icon>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Filles</p>
                    <h3 class="text-4xl font-black text-gray-800 dark:text-white mb-6">{{ number_format($stats['girls'] ?? 0) }}</h3>
                    <p class="text-[11px] font-bold text-gray-400">Total des filles</p>
                    <div class="absolute bottom-10 right-10 h-8 w-8 rounded-full bg-[#eab308] text-white flex items-center justify-center shadow-lg shadow-yellow-500/30 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform">
                        <iconify-icon icon="lucide:arrow-up-right" width="16"></iconify-icon>
                    </div>
                </div>
            </div>

            <!-- Gender Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 p-10 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 mt-10">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-xl font-black text-gray-800 dark:text-white tracking-tight italic">Répartition par genre</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Effectif comparé des garçons et des filles pour l'exercice 2025-2026.</p>
                    </div>
                </div>
                {{-- Chart container with fixed height to prevent collapse --}}
                <div id="students-gender-chart" class="w-full min-h-[350px]"></div>
            </div>
        </div>
    </x-backend.pages.students.partials.layout>
</x-layouts.modules-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.querySelector('#students-gender-chart');
        if (!el) return;

        const options = {
            series: [{
                name: 'Garçons',
                data: [{{ (int)($stats['boys'] ?? 0) }}]
            }, {
                name: 'Filles',
                data: [{{ (int)($stats['girls'] ?? 0) }}]
            }],
            chart: {
                type: 'bar',
                height: 350,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#0ea5e9', '#eab308'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 15,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                fontFamily: 'Inter',
                fontWeight: 900,
                fontSize: '12px'
            },
            xaxis: {
                categories: ['Effectifs Actuels'],
                labels: { style: { colors: '#94a3b8', fontWeight: 900 } }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
            },
            grid: { borderColor: '#f1f5f9' },
            tooltip: { theme: 'light' }
        };

        const chart = new ApexCharts(el, options);
        chart.render();
    });
</script>
@endpush
