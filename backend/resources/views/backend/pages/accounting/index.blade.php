@extends('backend.layouts.app')

@section('admin-content')
    @php
        $section = $section ?? 'dashboard';
        $meta = match ($section) {
            'account-plans' => [
                'title' => 'Plan comptable',
                'subtitle' => 'Comptes généraux et rattachements.',
                'icon' => 'lucide:list-ordered',
            ],
            'sub-account-plans' => [
                'title' => 'Sous-comptes',
                'subtitle' => 'Analytique rattachée au plan.',
                'icon' => 'lucide:list-tree',
            ],
            'fees' => [
                'title' => 'Frais & produits',
                'subtitle' => 'Barèmes par école et type.',
                'icon' => 'lucide:receipt',
            ],
            'currencies' => [
                'title' => 'Monnaies & taux',
                'subtitle' => 'Devises et taux de change actifs.',
                'icon' => 'lucide:coins',
            ],
            'payments' => [
                'title' => 'Paiements',
                'subtitle' => 'Encaissements et suivi des échéances.',
                'icon' => 'lucide:credit-card',
            ],
            'journal' => [
                'title' => 'Journal',
                'subtitle' => 'Écritures comptables.',
                'icon' => 'lucide:file-edit',
            ],
            'reports' => [
                'title' => 'États & rapports',
                'subtitle' => 'Synthèses et exports.',
                'icon' => 'lucide:bar-chart-big',
            ],
            default => [
                'title' => 'Comptabilité',
                'subtitle' => 'Pilotage financier (normes OHADA).',
                'icon' => 'lucide:calculator',
            ],
        };
        $breadcrumbCurrent = match ($section) {
            'dashboard' => 'Tableau de bord',
            'account-plans' => 'Plan comptable',
            'sub-account-plans' => 'Sous-comptes',
            'fees' => 'Frais & produits',
            'currencies' => 'Monnaies & taux',
            'payments' => 'Paiements',
            'journal' => 'Journal',
            'reports' => 'États & rapports',
            default => 'Comptabilité',
        };
    @endphp

    <x-admin.shadcn-shell module="accounting" :title="$meta['title']" :subtitle="$meta['subtitle']" :icon="$meta['icon']"
        :breadcrumb-current="$breadcrumbCurrent">
        @if ($section !== 'dashboard')
            <x-slot name="actions">
                <a href="{{ route('admin.accounting.index') }}"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 text-xs font-bold text-zinc-700 shadow-sm transition-colors hover:bg-zinc-50">
                    <iconify-icon icon="lucide:arrow-left" width="16"></iconify-icon>
                    Tableau de bord
                </a>
            </x-slot>
        @endif

        @include('backend.pages.accounting.sections.' . $section)
    </x-admin.shadcn-shell>
@endsection
