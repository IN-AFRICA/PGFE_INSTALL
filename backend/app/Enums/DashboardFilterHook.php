<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Points d'extension (hooks) disponibles sur le Dashboard backend.
 * Chaque constante représente un emplacement où des blocs HTML peuvent être injectés
 * via Hook::applyFilters(DashboardFilterHook::CONSTANT, $defaultValue).
 */
final class DashboardFilterHook
{
    public const DASHBOARD_AFTER_BREADCRUMBS = 'dashboard.after_breadcrumbs';

    public const DASHBOARD_CARDS_BEFORE_USERS = 'dashboard.cards_before_users';

    public const DASHBOARD_CARDS_AFTER_USERS = 'dashboard.cards_after_users';

    public const DASHBOARD_CARDS_AFTER_ROLES = 'dashboard.cards_after_roles';

    public const DASHBOARD_CARDS_AFTER_PERMISSIONS = 'dashboard.cards_after_permissions';

    public const DASHBOARD_CARDS_AFTER_TRANSLATIONS = 'dashboard.cards_after_translations';

    public const DASHBOARD_CARDS_AFTER = 'dashboard.cards_after';

    public const DASHBOARD_AFTER = 'dashboard.after';
}
