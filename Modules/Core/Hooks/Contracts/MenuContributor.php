<?php
// Modules/Core/Hooks/Contracts/MenuContributor.php
namespace Modules\Core\Hooks\Contracts;

use Modules\Core\Hooks\Registry\HookRegistry;

interface MenuContributor extends HookContributor {}

// Modules/Core/Hooks/Contracts/DashboardWidgetContributor.php
namespace Modules\Core\Hooks\Contracts;

interface DashboardWidgetContributor extends HookContributor {}

// Modules/Core/Hooks/Contracts/SettingsGroupContributor.php
namespace Modules\Core\Hooks\Contracts;

interface SettingsGroupContributor extends HookContributor {}

// Modules/Core/Hooks/Contracts/PermissionContributor.php
namespace Modules\Core\Hooks\Contracts;

interface PermissionContributor extends HookContributor {}

// Modules/Core/Hooks/Contracts/NotificationTypeContributor.php
namespace Modules\Core\Hooks\Contracts;

interface NotificationTypeContributor extends HookContributor {}
