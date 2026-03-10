<?php

namespace Modules\Users\Tests\Unit;

use App\Instances\Instance;
use App\Models\User;
use Modules\Users\Services\MembershipService;
use Modules\Users\Tests\TestCase;

final class MembershipServiceTest extends TestCase
{
    private function makeUser(string $email = 'member@test.com'): User
    {
        return User::create([
            'full_name' => 'Member',
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function makeInstance(string $slug = 'acme'): Instance
    {
        return Instance::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    public function test_add_update_remove_and_status(): void
    {
        $service = app(MembershipService::class);
        $user = $this->makeUser();
        $instance = $this->makeInstance();

        $service->addToInstance($user, $instance->id, 'active');
        $this->assertSame('active', $service->status($user, $instance->id));

        $service->updateStatus($user, $instance->id, 'disabled');
        $this->assertSame('disabled', $service->status($user, $instance->id));

        $service->removeFromInstance($user, $instance->id);
        $this->assertNull($service->status($user, $instance->id));
    }

    public function test_add_rejects_invalid_status(): void
    {
        $service = app(MembershipService::class);
        $user = $this->makeUser('bad1@test.com');
        $instance = $this->makeInstance('bad1');

        $this->expectException(\InvalidArgumentException::class);
        $service->addToInstance($user, $instance->id, 'nope');
    }

    public function test_update_rejects_invalid_status(): void
    {
        $service = app(MembershipService::class);
        $user = $this->makeUser('bad2@test.com');
        $instance = $this->makeInstance('bad2');

        $service->addToInstance($user, $instance->id, 'active');

        $this->expectException(\InvalidArgumentException::class);
        $service->updateStatus($user, $instance->id, 'wrong');
    }
}
