<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum des différents points d'extension (hooks) pour l'interface admin.
 * Chaque case possède une valeur string unique utilisée par Hook::applyFilters().
 */
enum AdminFilterHook: string
{
    case ADMIN_HEAD = 'admin.head';
    case ADMIN_FOOTER_BEFORE = 'admin.footer.before';
    case ADMIN_FOOTER_AFTER = 'admin.footer.after';

    // Sidebar
    case SIDEBAR_MENU_GROUP_BEFORE = 'admin.sidebar.menu.group.before';
    case SIDEBAR_MENU_GROUP_HEADING_BEFORE = 'admin.sidebar.menu.group.heading.before';
    case SIDEBAR_MENU_GROUP_HEADING_AFTER = 'admin.sidebar.menu.group.heading.after';
    case SIDEBAR_MENU_BEFORE_ALL = 'admin.sidebar.menu.before.all';
    case SIDEBAR_MENU_AFTER_ALL = 'admin.sidebar.menu.after.all';
    case SIDEBAR_MENU_GROUP_AFTER = 'admin.sidebar.menu.group.after';
    case SIDEBAR_MENU_ITEM_AFTER = 'admin.sidebar.menu.item.after';

    // Header / topbar
    case HEADER_RIGHT_MENU_BEFORE = 'admin.header.right_menu.before';
    case HEADER_BEFORE_LOCALE_SWITCHER = 'admin.header.before.locale_switcher';
    case HEADER_AFTER_LOCALE_SWITCHER = 'admin.header.after.locale_switcher';
    case DARK_MODE_TOGGLER_BEFORE_BUTTON = 'admin.header.dark_mode_toggler.before';
    case DARK_MODE_TOGGLER_AFTER_BUTTON = 'admin.header.dark_mode_toggler.after';
    case HEADER_AFTER_ACTIONS = 'admin.header.after.actions';
    case USER_DROPDOWN_BEFORE = 'admin.header.user_dropdown.before';
}
