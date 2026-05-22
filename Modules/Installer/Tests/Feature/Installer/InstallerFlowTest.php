<?php

namespace Modules\Installer\Tests\Feature\Installer;

use Modules\Installer\Services\InstallerRunner;
use Modules\Installer\Tests\TestCase;

final class InstallerFlowTest extends TestCase
{
    private function seedValidSession(): void
    {
        $this->withSession([
            'installer' => [
                'steps' => [1 => true, 2 => true, 3 => true, 4 => true],
                'db_confirmed' => true,
                'config_confirmed' => true,
                'admin_confirmed' => true,
            ],
        ]);
    }

    public function test_start_install_requires_all_steps_and_flags(): void
    {
        $this->postJson('/install/start', [])
            ->assertStatus(422);
    }

    public function test_start_install_returns_signed_stream_url(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->seedValidSession();

        $response = $this->postJson('/install/start');

        $response->assertOk()
            ->assertJsonStructure(['ok', 'stream_url'])
            ->assertJsonFragment(['ok' => true]);
    }

    public function test_stream_install_runs_and_returns_events(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->seedValidSession();

        $start = $this->postJson('/install/start');
        $streamUrl = (string) $start->json('stream_url');

        $this->app->instance(InstallerRunner::class, new class {
            public function run(array $data, callable $callback): void
            {
                $callback(25, 'Step 1');
                $callback(50, 'Step 2');
            }
        });

        $path = parse_url($streamUrl, PHP_URL_PATH) ?? '/install/stream';
        $query = parse_url($streamUrl, PHP_URL_QUERY);
        $url = $path . ($query ? ('?' . $query) : '');

        $response = $this->get($url);

        $response->assertOk();
        $this->assertStringStartsWith('text/event-stream', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('event: done', $content);
    }
}
