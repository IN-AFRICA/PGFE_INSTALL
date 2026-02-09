<?php

declare(strict_types=1);

namespace App\Services\MenuService;

use Illuminate\Support\Facades\Request;

final class AdminMenuService
{
    /**
     * Retourne un tableau associatif: group => [AdminMenuItem, ...]
     * Ici on ne définit que le module Academic Levels (peut être étendu ensuite).
     *
     * @return array<string, AdminMenuItem[]>
     */
    public function getMenu(): array
    {
        $groups = [];
        $user = auth()->user();
        $selectedSchoolId = session('selected_school_id');

        $groups['Dashboard'] = [
            new AdminMenuItem([
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'lucide:layout-dashboard',
                'route' => $this->getRouteWithContext('admin.dashboard'),
                'active' => $this->isCurrentRoutePrefixed('admin.dashboard'),
            ]),
        ];

        $groups['Pays'] = [
            new AdminMenuItem([
                'id' => 'countries',
                'label' => 'Pays',
                'icon' => 'lucide:globe',
                'route' => $this->getRouteWithContext('admin.countries.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.countries.'),
            ]),
        ];

        $groups['Utilisateurs'] = [
            new AdminMenuItem([
                'id' => 'users',
                'label' => 'Utilisateurs',
                'icon' => 'lucide:user',
                'route' => $this->getRouteWithContext('admin.users.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.users.'),
            ]),
            new AdminMenuItem([
                'id' => 'roles',
                'label' => 'Rôles & Permissions',
                'icon' => 'lucide:shield-check',
                'route' => $this->getRouteWithContext('admin.roles.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.roles.'),
            ]),
        ];

        // Menus contextuels, affichés uniquement si une école est sélectionnée
        // Menus contextuels, affichés si une école est sélectionnée OU pour le super-admin
        if ($selectedSchoolId || ($user && $user->hasRole('super-admin'))) {
            // Groupe Mois, Infrastructures, Stock (refactorisé)
            $groups['Gestion'] = [
                new AdminMenuItem([
                    'id' => 'mois',
                    'label' => 'Mois',
                    'icon' => 'lucide:calendar-days',
                    'route' => $this->getRouteWithContext('admin.mois.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.mois.'),
                ]),
                new AdminMenuItem([
                    'id' => 'infrastructures',
                    'label' => 'Infrastructures',
                    'icon' => 'lucide:building',
                    'route' => $this->getRouteWithContext('admin.infra-infrastructures.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.infra-'),
                    'children' => [
                        new AdminMenuItem([
                            'id' => 'infra-dashboard',
                            'label' => 'Tableau de bord',
                            'icon' => 'lucide:layout-dashboard',
                            'route' => $this->getRouteWithContext('admin.infra.dashboard'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra.dashboard'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-categories',
                            'label' => 'Catégories',
                            'icon' => 'lucide:tag',
                            'route' => $this->getRouteWithContext('admin.infra-categories.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-categories.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-bailleurs',
                            'label' => 'Bailleurs',
                            'icon' => 'lucide:handshake',
                            'route' => $this->getRouteWithContext('admin.infra-bailleurs.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-bailleurs.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-infrastructures',
                            'label' => 'Infrastructures',
                            'icon' => 'lucide:building-2',
                            'route' => $this->getRouteWithContext('admin.infra-infrastructures.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-infrastructures.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-infrastructure-inventaires',
                            'label' => 'Suivi Bâtiments',
                            'icon' => 'lucide:clipboard-check',
                            'route' => $this->getRouteWithContext('admin.infra-infrastructure-inventaires.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-infrastructure-inventaires.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-equipements',
                            'label' => 'Équipements',
                            'icon' => 'lucide:cpu',
                            'route' => $this->getRouteWithContext('admin.infra-equipements.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-equipements.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-inventaires',
                            'label' => 'Suivi Équipements',
                            'icon' => 'lucide:clipboard-list',
                            'route' => $this->getRouteWithContext('admin.infra-inventaires.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-inventaires.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'infra-etats',
                            'label' => 'Signalements',
                            'icon' => 'lucide:alert-triangle',
                            'route' => $this->getRouteWithContext('admin.infra-etats.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.infra-etats.'),
                        ]),
                    ],
                ]),
                new AdminMenuItem([
                    'id' => 'stock',
                    'label' => 'Stock',
                    'icon' => 'lucide:boxes',
                    'route' => $this->getRouteWithContext('admin.stock-articles.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.stock-'),
                    'children' => [
                        new AdminMenuItem([
                            'id' => 'stock-articles',
                            'label' => 'Articles',
                            'icon' => 'lucide:package',
                            'route' => $this->getRouteWithContext('admin.stock-articles.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-articles.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-categories',
                            'label' => 'Catégories',
                            'icon' => 'lucide:tag',
                            'route' => $this->getRouteWithContext('admin.stock-categories.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-categories.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-providers',
                            'label' => 'Fournisseurs',
                            'icon' => 'lucide:truck',
                            'route' => $this->getRouteWithContext('admin.stock-providers.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-providers.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-entries',
                            'label' => 'Entrées',
                            'icon' => 'lucide:arrow-down-circle',
                            'route' => $this->getRouteWithContext('admin.stock-entries.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-entries.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-exits',
                            'label' => 'Sorties',
                            'icon' => 'lucide:arrow-up-circle',
                            'route' => $this->getRouteWithContext('admin.stock-exits.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-exits.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-states',
                            'label' => 'États',
                            'icon' => 'lucide:check-square',
                            'route' => $this->getRouteWithContext('admin.stock-states.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-states.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'stock-inventories',
                            'label' => 'Inventaires',
                            'icon' => 'lucide:clipboard-list',
                            'route' => $this->getRouteWithContext('admin.stock-inventories.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.stock-inventories.'),
                        ]),
                    ],
                ]),
            ];
            $groups['Écoles'] = [
                new AdminMenuItem([
                    'id' => 'schools',
                    'label' => 'Écoles',
                    'icon' => 'mdi:school',
                    'route' => $this->getRouteWithContext('admin.schools.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.schools.'),
                    'children' => [
                        new AdminMenuItem([
                            'id' => 'filieres',
                            'label' => 'Filières',
                            'icon' => 'lucide:git-branch',
                            'route' => $this->getRouteWithContext('admin.filiaires.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.filiaires.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'academic-levels',
                            'label' => 'Niveaux',
                            'icon' => 'lucide:layers-3',
                            'route' => $this->getRouteWithContext('admin.academic-levels.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.academic-levels.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'classrooms',
                            'label' => 'Classes',
                            'icon' => 'lucide:layers',
                            'route' => $this->getRouteWithContext('admin.classrooms.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.classrooms.'),
                        ]),
                    ],
                ]),
            ];
            $groups['Élèves'] = [
                new AdminMenuItem([
                    'id' => 'students',
                    'label' => 'Élèves',
                    'icon' => 'lucide:users',
                    'route' => $this->getRouteWithContext('admin.students.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.students.'),
                ]),
            ];

            // Groupe Comptabilité — accès aux principaux modules de compta (aligné avec les APIs)
            $groups['Comptabilité'] = [
                new AdminMenuItem([
                    'id' => 'accounting',
                    'label' => 'Comptabilité',
                    'icon' => 'lucide:banknote',
                    'route' => $this->getRouteWithContext('admin.accounting.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                    'children' => [
                        new AdminMenuItem([
                            'id' => 'accounting-dashboard',
                            'label' => 'Tableau de bord',
                            'icon' => 'lucide:layout-dashboard',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'dashboard']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'account-plans',
                            'label' => 'Plan comptable',
                            'icon' => 'lucide:list-ordered',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'account-plans']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'sub-account-plans',
                            'label' => 'Sous-comptes',
                            'icon' => 'lucide:list-tree',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'sub-account-plans']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'fees',
                            'label' => 'Frais & produits',
                            'icon' => 'lucide:receipt-euro',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'fees']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'currencies',
                            'label' => 'Monnaies & taux',
                            'icon' => 'lucide:coins',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'currencies']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'payments',
                            'label' => 'Paiements',
                            'icon' => 'lucide:credit-card',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'payments']),
                            'active' => $this->isCurrentRoutePrefixed('admin.accounting.'),
                        ]),
                    ],
                ]),
            ];

            // Groupe Année scolaire — création/activation année, semestres, périodes
            $groups['Année scolaire'] = [
                new AdminMenuItem([
                    'id' => 'school-year',
                    'label' => 'Année scolaire',
                    'icon' => 'lucide:calendar-days',
                    'route' => $this->getRouteWithContext('admin.school-years.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.school-years.'),
                    'children' => [
                        new AdminMenuItem([
                            'id' => 'school-year-create',
                            'label' => 'Créer une année',
                            'icon' => 'lucide:plus-circle',
                            'route' => $this->getRouteWithContext('admin.school-years.create'),
                            'active' => $this->isCurrentRoutePrefixed('admin.school-years.create'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'school-year-activate',
                            'label' => 'Activer une année',
                            'icon' => 'lucide:check-circle',
                            'route' => $this->getRouteWithContext('admin.school-years.activate', ['id' => \App\Models\SchoolYear::first()?->id ?? 1]),
                            'active' => $this->isCurrentRoutePrefixed('admin.school-years.activate'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'semesters',
                            'label' => 'Semestres',
                            'icon' => 'lucide:calendar-range',
                            'route' => $this->getRouteWithContext('admin.semesters.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.semesters.'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'periods',
                            'label' => 'Périodes',
                            'icon' => 'lucide:clock',
                            'route' => $this->getRouteWithContext('admin.periods.index'),
                            'active' => $this->isCurrentRoutePrefixed('admin.periods.'),
                        ]),
                    ],
                ]),
            ];
        }

        return $groups;
    }

    public function render(array $items): string
    {
        $html = '';
        $user = auth()->user();
        $selectedSchoolId = session('selected_school_id');
        foreach ($items as $item) {
            // Si l'item concerne une école et le super admin n'a pas sélectionné d'école, on masque
            if (isset($item->requiresSchool) && $item->requiresSchool === true) {
                if ($user && $user->hasRole('super-admin') && ! $selectedSchoolId) {
                    continue;
                }
            }
            $html .= view('backend.layouts.partials.sidebar.menu-item', ['item' => $item])->render();
        }

        return $html;
    }

    public function shouldExpandSubmenu(AdminMenuItem $item): bool
    {
        if (empty($item->children)) {
            return false;
        }
        foreach ($item->children as $child) {
            if ($child->active) {
                return true;
            }
        }

        return false;
    }

    private function getRouteWithContext(string $name, array $params = []): string
    {
        $selectedSchoolId = session('selected_school_id');
        if ($selectedSchoolId && !isset($params['school_id'])) {
            $params['school_id'] = $selectedSchoolId;
        }
        
        try {
            return route($name, $params);
        } catch (\Exception $e) {
            return '#';
        }
    }

    private function isCurrentRoutePrefixed(string $prefix): bool
    {
        $current = Request::route()?->getName();

        return $current !== null && str_starts_with($current, $prefix);
    }
}
