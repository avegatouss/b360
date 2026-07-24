<?php

declare(strict_types=1);

namespace Modules\Couture360\Tests\Feature;

use App\Models\User;
use Modules\Couture360\Tests\TestCase;

final class DeviceAuthTest extends TestCase
{
    /** @return array<string, mixed> */
    private function loginPayload(User $user, int|string $instanceId): array
    {
        return [
            'email' => $user->email,
            'password' => 'password',
            'instance_id' => $instanceId,
            'device_name' => 'Pixel-7',
        ];
    }

    public function test_logs_in_a_member_and_returns_a_token(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $res = $this->postJson('/api/couture/auth/login', $this->loginPayload($user, $instance->id));

        $res->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email'], 'instance_id', 'permissions']);
        $this->assertNotEmpty($res->json('token'));
    }

    public function test_rejects_bad_credentials_with_401(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);

        $this->postJson('/api/couture/auth/login', [
            ...$this->loginPayload($user, $instance->id),
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_rejects_a_non_member_with_403(): void
    {
        $instanceA = $this->makeInstance('atelier-a');
        $instanceB = $this->makeInstance('atelier-b');
        $user = $this->makeMember($instanceA, 'agent-a@test.com');

        $this->postJson('/api/couture/auth/login', $this->loginPayload($user, $instanceB->id))
            ->assertStatus(403);
    }

    public function test_returns_profile_on_me_with_a_valid_token(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);
        $token = $this->postJson('/api/couture/auth/login', $this->loginPayload($user, $instance->id))->json('token');

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/couture/me')
            ->assertOk()
            ->assertJson(['user' => ['id' => $user->id], 'instance_id' => (int) $instance->id]);
    }

    public function test_rejects_me_without_a_token(): void
    {
        $this->getJson('/api/couture/me')->assertStatus(401);
    }

    public function test_logout_deletes_the_token(): void
    {
        $instance = $this->makeInstance();
        $user = $this->makeMember($instance);
        $token = $this->postJson('/api/couture/auth/login', $this->loginPayload($user, $instance->id))->json('token');

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/api/couture/auth/logout')->assertNoContent();

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/couture/me')->assertStatus(401);
    }
}
