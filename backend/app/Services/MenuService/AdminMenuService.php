<?php

declare(strict_types=1);

namespace App\Services\MenuService;

use Exception;
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
            new AdminMenuItem([
                'id' => 'school-years',
                'label' => 'Années scolaires',
                'icon' => 'lucide:calendar-days',
                'route' => $this->getRouteWithContext('admin.school-years.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.school-years.'),
            ]),
            new AdminMenuItem([
                'id' => 'classrooms',
                'label' => 'Classes',
                'icon' => 'lucide:layers',
                'route' => $this->getRouteWithContext('admin.classrooms.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.classrooms.'),
            ]),
            new AdminMenuItem([
                'id' => 'academic-levels',
                'label' => 'Niveaux',
                'icon' => 'lucide:layers-3',
                'route' => $this->getRouteWithContext('admin.academic-levels.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.academic-levels.'),
            ]),
            new AdminMenuItem([
                'id' => 'semesters',
                'label' => 'Semestres',
                'icon' => 'lucide:calendar-range',
                'route' => $this->getRouteWithContext('admin.semesters.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.semesters.'),
            ]),
            new AdminMenuItem([
                'id' => 'mois',
                'label' => 'Mois',
                'icon' => 'lucide:calendar-days',
                'route' => $this->getRouteWithContext('admin.mois.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.mois.'),
            ]),
            new AdminMenuItem([
                'id' => 'cycles',
                'label' => 'Cycles',
                'icon' => 'lucide:repeat',
                'route' => $this->getRouteWithContext('admin.cycles.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.cycles.'),
            ]),
            new AdminMenuItem([
                'id' => 'filieres',
                'label' => 'Filières',
                'icon' => 'lucide:git-branch',
                'route' => $this->getRouteWithContext('admin.filiaires.index'),
                'active' => $this->isCurrentRoutePrefixed('admin.filiaires.'),
            ]),
        ];

        // Menus contextuels, affichés si une école est sélectionnée OU pour le super-admin
        if ($selectedSchoolId || ($user && $user->hasRole('super-admin'))) {
            // Groupe Mois (stock = groupe dédié)
            $groups['Gestion'] = [
                new AdminMenuItem([
                    'id' => 'mois',
                    'label' => 'Mois',
                    'icon' => 'lucide:calendar-days',
                    'route' => $this->getRouteWithContext('admin.mois.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.mois.'),
                ]),
            ];

            $groups['Stock'] = [
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
            ];

            $groups['Infrastructures'] = [
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
                    'label' => 'Bâtiments & ouvrages',
                    'icon' => 'lucide:building-2',
                    'route' => $this->getRouteWithContext('admin.infra-infrastructures.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.infra-infrastructures.'),
                ]),
                new AdminMenuItem([
                    'id' => 'infra-infrastructure-inventaires',
                    'label' => 'Suivi bâtiments',
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
                    'label' => 'Suivi équipements',
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
            ];

            $groups['Écoles'] = [
                new AdminMenuItem([
                    'id' => 'schools',
                    'label' => 'Écoles',
                    'icon' => 'mdi:school',
                    'route' => $this->getRouteWithContext('admin.schools.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.schools.')
                        || $this->isCurrentRoutePrefixed('admin.filiaires.')
                        || $this->isCurrentRoutePrefixed('admin.academic-levels.')
                        || $this->isCurrentRoutePrefixed('admin.classrooms.'),
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
                    'id' => 'students-visits',
                    'label' => 'Visites de classe',
                    'icon' => 'lucide:eye',
                    'route' => $this->getRouteWithContext('admin.visits.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.visits.'),
                ]),
                new AdminMenuItem([
                    'id' => 'students-fiche-cotation',
                    'label' => 'Fiches de cotation',
                    'icon' => 'lucide:clipboard-list',
                    'route' => $this->getRouteWithContext('admin.fiche-cotations.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.fiche-cotations.'),
                ]),
                new AdminMenuItem([
                    'id' => 'students-deliberations',
                    'label' => 'Délibérations',
                    'icon' => 'lucide:scale',
                    'route' => $this->getRouteWithContext('admin.deliberations.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.deliberations.'),
                ]),
                new AdminMenuItem([
                    'id' => 'students-exits',
                    'label' => 'Sorties de classe',
                    'icon' => 'lucide:log-out',
                    'route' => $this->getRouteWithContext('admin.student-exits.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.student-exits.'),
                ]),
                new AdminMenuItem([
                    'id' => 'students-inscriptions',
                    'label' => 'Inscriptions',
                    'icon' => 'lucide:edit-3',
                    'route' => $this->getRouteWithContext('admin.registrations.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.registrations.'),
                ]),
                new AdminMenuItem([
                    'id' => 'students-presences',
                    'label' => 'Présences',
                    'icon' => 'lucide:calendar-check',
                    'route' => $this->getRouteWithContext('admin.presences.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.presences.'),
                ]),
            ];

            $groups['Ressources Humaines'] = [
                new AdminMenuItem([
                    'id' => 'rh-personnels',
                    'label' => 'Personnels',
                    'icon' => 'lucide:id-card',
                    'route' => $this->getRouteWithContext('admin.personnels.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.personnels.'),
                ]),
                new AdminMenuItem([
                    'id' => 'rh-roles',
                    'label' => 'Rôles & assignation',
                    'icon' => 'lucide:shield-check',
                    'route' => $this->getRouteWithContext('admin.roles.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.roles.'),
                ]),
                new AdminMenuItem([
                    'id' => 'rh-personnel-presences',
                    'label' => 'Présences personnel',
                    'icon' => 'lucide:calendar-check-2',
                    'route' => $this->getRouteWithContext('admin.personnel-presences.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.personnel-presences.'),
                ]),
                new AdminMenuItem([
                    'id' => 'rh-affectations',
                    'label' => 'Affectations',
                    'icon' => 'lucide:map-pin',
                    'route' => $this->getRouteWithContext('admin.person-affectations.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.person-affectations.'),
                ]),
            ];

            // Groupe Comptabilité — entrées plates (comme Infrastructure / RH)
            $groups['Comptabilité'] = [
                        new AdminMenuItem([
                            'id' => 'accounting-dashboard',
                            'label' => 'Tableau de bord',
                            'icon' => 'lucide:layout-dashboard',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'dashboard']),
                    'active' => $this->isAccountingSection('dashboard'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'account-plans',
                            'label' => 'Plan comptable',
                            'icon' => 'lucide:list-ordered',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'account-plans']),
                    'active' => $this->isAccountingSection('account-plans'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'sub-account-plans',
                            'label' => 'Sous-comptes',
                            'icon' => 'lucide:list-tree',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'sub-account-plans']),
                    'active' => $this->isAccountingSection('sub-account-plans'),
                ]),
                new AdminMenuItem([
                    'id' => 'accounting-journal',
                    'label' => 'Journal',
                    'icon' => 'lucide:file-edit',
                    'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'journal']),
                    'active' => $this->isAccountingSection('journal'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'fees',
                            'label' => 'Frais & produits',
                    'icon' => 'lucide:receipt',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'fees']),
                    'active' => $this->isAccountingSection('fees'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'currencies',
                            'label' => 'Monnaies & taux',
                            'icon' => 'lucide:coins',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'currencies']),
                    'active' => $this->isAccountingSection('currencies'),
                        ]),
                        new AdminMenuItem([
                            'id' => 'payments',
                            'label' => 'Paiements',
                            'icon' => 'lucide:credit-card',
                            'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'payments']),
                    'active' => $this->isAccountingSection('payments'),
                ]),
                new AdminMenuItem([
                    'id' => 'accounting-reports',
                    'label' => 'États & rapports',
                    'icon' => 'lucide:bar-chart-big',
                    'route' => $this->getRouteWithContext('admin.accounting.index', ['section' => 'reports']),
                    'active' => $this->isAccountingSection('reports'),
                ]),
            ];

            // Groupe Pédagogie — planification des cours et activités scolaires
            $groups['Pédagogie'] = [
                new AdminMenuItem([
                    'id' => 'planning',
                    'label' => 'Planification des cours',
                    'icon' => 'lucide:calendar-clock',
                    'route' => $this->getRouteWithContext('admin.planning.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.planning.'),
                ]),
                new AdminMenuItem([
                    'id' => 'activities',
                    'label' => 'Activités scolaires',
                    'icon' => 'lucide:party-popper',
                    'route' => $this->getRouteWithContext('admin.activities.index'),
                    'active' => $this->isCurrentRoutePrefixed('admin.activities.'),
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

        // Filtrage contextuel : dans chaque module, la sidebar ne doit afficher
        // que les liens de ce module (plus aucun "mega-menu" global).
        $current = Request::route()?->getName() ?? '';
        $restrictGestionRootId = null;

        if ($current !== '') {
            // Module Élèves (élèves, présences, fiches, délibérations, sorties, visites)
            if (
                str_starts_with($current, 'admin.students.') ||
                str_starts_with($current, 'admin.presences.') ||
                str_starts_with($current, 'admin.fiche-cotations.') ||
                str_starts_with($current, 'admin.deliberations.') ||
                str_starts_with($current, 'admin.student-exits.') ||
                str_starts_with($current, 'admin.visits.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Élèves']));
            }
            // Module Ressources Humaines (personnels, rôles, présences agents, affectations)
            elseif (
                str_starts_with($current, 'admin.personnels.') ||
                str_starts_with($current, 'admin.roles.') ||
                str_starts_with($current, 'admin.personnel-presences.') ||
                str_starts_with($current, 'admin.person-affectations.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Ressources Humaines']));
            }
            // Module Écoles (écoles, filières, niveaux, classes)
            elseif (
                str_starts_with($current, 'admin.schools.') ||
                str_starts_with($current, 'admin.filiaires.') ||
                str_starts_with($current, 'admin.academic-levels.') ||
                str_starts_with($current, 'admin.classrooms.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Écoles']));
            }
            // Module Comptabilité
            elseif (str_starts_with($current, 'admin.accounting.')) {
                $groups = array_intersect_key($groups, array_flip(['Comptabilité']));
            }
            // Module Pédagogie (planning & activités)
            elseif (
                str_starts_with($current, 'admin.planning.') ||
                str_starts_with($current, 'admin.activities.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Pédagogie']));
            }
            // Module Année scolaire (année, semestres, périodes — mois = groupe Gestion)
            elseif (
                str_starts_with($current, 'admin.school-years.') ||
                str_starts_with($current, 'admin.semesters.') ||
                str_starts_with($current, 'admin.periods.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Année scolaire']));
            }
            // Module Infrastructures (dashboard + infra-*)
            elseif (
                str_starts_with($current, 'admin.infra.dashboard') ||
                str_starts_with($current, 'admin.infra-')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Infrastructures']));
            }
            // Module Stock
            elseif (str_starts_with($current, 'admin.stock-')) {
                $groups = array_intersect_key($groups, array_flip(['Stock']));
            }
            // Module Mois / Horaire (mois index seul)
            elseif (str_starts_with($current, 'admin.mois.')) {
                $groups = array_intersect_key($groups, array_flip(['Gestion']));
                $restrictGestionRootId = 'mois';
            }
            // Module Utilisateurs (comptes utilisateurs ; les rôles sont regroupés sous RH)
            elseif (str_starts_with($current, 'admin.users.')) {
                $groups = array_intersect_key($groups, array_flip(['Utilisateurs']));
            }
            // Module Pays (pays / provinces / communes / territoires)
            elseif (
                str_starts_with($current, 'admin.countries.') ||
                str_starts_with($current, 'admin.provinces.') ||
                str_starts_with($current, 'admin.communes.') ||
                str_starts_with($current, 'admin.territories.')
            ) {
                $groups = array_intersect_key($groups, array_flip(['Pays']));
            }
            // Dashboard & autres: on garde le menu complet
        }

        // Si on est dans un sous-module de "Gestion", on restreint au Mois.
        if ($restrictGestionRootId !== null && isset($groups['Gestion'])) {
            $groups['Gestion'] = array_values(array_filter(
                $groups['Gestion'],
                fn (AdminMenuItem $item) => $item->id === $restrictGestionRootId
            ));
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
        if ($selectedSchoolId && ! isset($params['school_id'])) {
            $params['school_id'] = $selectedSchoolId;
        }
        
        try {
            return route($name, $params);
        } catch (Exception $e) {
            return '#';
        }
    }

    private function isCurrentRoutePrefixed(string $prefix): bool
    {
        $current = Request::route()?->getName();

        return $current !== null && str_starts_with($current, $prefix);
    }

    /**
     * Rubrique active du module Comptabilité (query ?section= sur admin.accounting.index).
     */
    private function isAccountingSection(string $section): bool
    {
        $name = Request::route()?->getName() ?? '';
        if (! str_starts_with($name, 'admin.accounting')) {
            return false;
        }
        if ($name === 'admin.accounting.index') {
            return request()->query('section', 'dashboard') === $section;
        }

        return $section === 'dashboard';
    }
}
