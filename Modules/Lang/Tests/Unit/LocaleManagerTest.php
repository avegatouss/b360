<?php

namespace Modules\Lang\Tests\Unit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Lang\Services\LocaleManager;
use Modules\Lang\Tests\TestCase;

final class LocaleManagerTest extends TestCase
{
    private LocaleManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(LocaleManager::class);
    }

    public function test_resolve_returns_config_default_when_no_setting(): void
    {
        config(['lang.default' => 'fr']);

        $this->assertSame('fr', $this->manager->resolve());
    }

    public function test_resolve_returns_session_locale_if_set(): void
    {
        Session::put('locale', 'en');

        $this->assertSame('en', $this->manager->resolve());
    }

    public function test_resolve_ignores_unsupported_session_locale(): void
    {
        Session::put('locale', 'jp');
        config(['lang.default' => 'fr', 'lang.supported' => ['fr', 'en']]);

        $this->assertSame('fr', $this->manager->resolve());
    }

    public function test_resolve_returns_global_setting_if_set(): void
    {
        DB::connection('system')->table('settings')->insert([
            'instance_id' => 0,
            'group' => 'lang',
            'key' => 'locale',
            'value' => 'en',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('en', $this->manager->resolve());
    }

    public function test_switch_stores_in_session_and_sets_app_locale(): void
    {
        $result = $this->manager->switch('en');

        $this->assertTrue($result);
        $this->assertSame('en', Session::get('locale'));
        $this->assertSame('en', App::getLocale());
    }

    public function test_switch_rejects_unsupported_locale(): void
    {
        $result = $this->manager->switch('jp');

        $this->assertFalse($result);
    }

    public function test_apply_sets_app_locale(): void
    {
        Session::put('locale', 'en');

        $this->manager->apply();

        $this->assertSame('en', App::getLocale());
    }

    public function test_supported_returns_config_locales(): void
    {
        config(['lang.supported' => ['fr', 'en']]);

        $this->assertSame(['fr', 'en'], $this->manager->supported());
    }

    public function test_is_supported_checks_correctly(): void
    {
        config(['lang.supported' => ['fr', 'en']]);

        $this->assertTrue($this->manager->isSupported('fr'));
        $this->assertTrue($this->manager->isSupported('en'));
        $this->assertFalse($this->manager->isSupported('de'));
    }

    public function test_label_returns_locale_label(): void
    {
        config(['lang.labels' => ['fr' => 'Francais', 'en' => 'English']]);

        $this->assertSame('Francais', $this->manager->label('fr'));
        $this->assertSame('English', $this->manager->label('en'));
    }
}
