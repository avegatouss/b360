<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Hooks\DTO\SettingsGroup;
use PHPUnit\Framework\TestCase;

final class SettingsGroupTest extends TestCase
{
    public function test_constructor_with_required_params(): void
    {
        $group = new SettingsGroup(id: 'general', label: 'General', view: 'settings::general');

        $this->assertSame('general', $group->id);
        $this->assertSame('General', $group->label);
        $this->assertSame('settings::general', $group->view);
        $this->assertSame(0, $group->priority);
        $this->assertNull($group->requiredPermission);
        $this->assertNull($group->requiredModule);
        $this->assertNull($group->visibleWhen);
    }

    public function test_constructor_with_all_params(): void
    {
        $cb = fn () => true;
        $group = new SettingsGroup(
            id: 'instances',
            label: 'Instances',
            view: 'instances::settings',
            priority: 900,
            requiredPermission: 'settings.manage',
            requiredModule: 'Instances',
            visibleWhen: $cb,
        );

        $this->assertSame('instances', $group->id);
        $this->assertSame(900, $group->priority);
        $this->assertSame('settings.manage', $group->requiredPermission);
        $this->assertSame('Instances', $group->requiredModule);
        $this->assertSame($cb, $group->visibleWhen);
    }
}
