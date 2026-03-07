<?php

namespace Modules\ModuleManager\Tests\Feature;

use Modules\ModuleManager\Tests\TestCase;

final class ModuleManagerAccessTest extends TestCase
{
    public function test_index_is_accessible_for_root_super_admin(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/modules")
            ->assertOk();
    }

    public function test_show_returns_404_for_unknown_module(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/modules/UnknownModule")
            ->assertStatus(404);
    }

    public function test_show_returns_ok_for_core_module(): void
    {
        $root = $this->makeRootInstance();
        $user = $this->makeRootSuperAdmin($root);

        $this->actingAs($user)
            ->get("/i/{$root->slug}/modules/Core")
            ->assertOk();
    }
}
