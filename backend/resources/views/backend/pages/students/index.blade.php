@extends('backend.layouts.app')

@section('title', 'Dashboard Élèves')

@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')]]" current="Dashboard Élèves" />
    <div class="space-y-8 max-w-7xl mx-auto py-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 pb-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Dashboard Élèves</h1>
                <p class="text-zinc-500 text-sm mt-1 font-medium italic">Analyse des effectifs et répartition par genre.</p>
            </div>

            {{-- Actions de filtre style Shadcn --}}
            <div class="flex items-center gap-2">
                <button class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-xs font-bold text-zinc-900 shadow-sm hover:bg-zinc-50 transition-colors uppercase tracking-widest">
                    <iconify-icon icon="lucide:sliders-horizontal" class="mr-2"></iconify-icon>
                    Filtres
                </button>
                <div class="h-9 flex items-center gap-2 rounded-md bg-zinc-900 px-4 text-[10px] font-bold text-white uppercase tracking-[0.15em] shadow-lg shadow-zinc-200">
                    <span>Session 2025-2026</span>
                    <iconify-icon icon="lucide:check-circle-2" class="text-emerald-400"></iconify-icon>
                </div>
            </div>
        </div>

        {{-- Stats Matrix --}}
        <div class="grid gap-4 md:grid-cols-3">
            @php
                $cards = [
                    ['label' => 'Total Apprenants', 'val' => $stats['total'], 'icon' => 'lucide:users', 'color' => 'zinc'],
                    ['label' => 'Garçons', 'val' => $stats['boys'], 'icon' => 'lucide:user', 'color' => 'blue'],
                    ['label' => 'Filles', 'val' => $stats['girls'], 'icon' => 'lucide:user-2', 'color' => 'amber'],
                ];
            @endphp

            @foreach($cards as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-all hover:border-zinc-300">
                    <div class="flex items-center justify-between space-y-0 pb-4">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-zinc-500">{{ $card['label'] }}</h3>
                        <div class="h-8 w-8 rounded-lg border border-zinc-100 bg-zinc-50 flex items-center justify-center text-zinc-400">
                            <iconify-icon icon="{{ $card['icon'] }}" class="text-lg"></iconify-icon>
                        </div>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-4xl font-black tracking-tighter text-zinc-900">
                            {{ number_format($card['val'] ?? 0) }}
                        </div>
                        <div class="text-[10px] font-bold text-zinc-400 uppercase">Live Data</div>
                    </div>
                    <div class="mt-4 h-1.5 w-full bg-zinc-100 rounded-full overflow-hidden">
                        <div class="h-full bg-zinc-900 transition-all duration-1000" style="width: 70%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Chart Section --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
                <div class="space-y-1">
                    <h2 class="text-xl font-bold tracking-tight text-zinc-900">Répartition Démographique</h2>
                    <p class="text-xs text-zinc-500 font-medium">Comparaison des effectifs par genre pour l'année académique.</p>
                </div>
                {{-- Legend Custom Shadcn-like --}}
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-sm bg-zinc-900"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Garçons</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-sm bg-zinc-400"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Filles</span>
                    </div>
                </div>
            </div>

            {{-- Chart container --}}
            <div id="students-gender-chart" class="w-full min-h-[400px]"></div>
        </div>
    </div>
@endsection

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
                    height: 400,
                    fontFamily: 'Geist, ui-sans-serif, system-ui',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: ['#18181b', '#a1a1aa'], // Zinc-900 et Zinc-400 (Pur Shadcn)
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '40%',
                        borderRadius: 4, // Arrondis subtils façon Shadcn
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: { fontSize: '12px', colors: ["#18181b"], fontWeight: 800 }
                },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: ['Effectifs Actuels'],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#71717a', fontWeight: 600, fontSize: '11px' } }
                },
                yaxis: {
                    show: true,
                    labels: { style: { colors: '#71717a', fontWeight: 500 } }
                },
                grid: {
                    borderColor: '#f4f4f5',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                },
                legend: { show: false }, // On utilise notre propre légende en haut
                tooltip: {
                    theme: 'light',
                    style: { fontSize: '12px' },
                    y: { formatter: (val) => val + " Élèves" }
                }
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        });
    </script>
@endpush
