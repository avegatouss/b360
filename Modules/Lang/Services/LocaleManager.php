<?php

namespace Modules\Lang\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Modules\Core\Support\CurrentInstance;

final class LocaleManager
{
    /**
     * Resolve the active locale.
     * Priority: session → instance setting → root setting → config.
     */
    public function resolve(): string
    {
        // 1. Session override (user switched language manually)
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && $this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        // 2. Instance setting (if instance context exists)
        if (function_exists('setting')) {
            $instance = CurrentInstance::get();
            if ($instance) {
                $instanceLocale = setting('lang.locale', null, $instance->id);
                if ($instanceLocale && $this->isSupported($instanceLocale)) {
                    return $instanceLocale;
                }
            }

            // 3. Root/global setting
            $globalLocale = setting('lang.locale');
            if ($globalLocale && $this->isSupported($globalLocale)) {
                return $globalLocale;
            }
        }

        // 4. Config default
        return config('lang.default', 'fr');
    }

    /**
     * Apply the resolved locale to the application.
     */
    public function apply(): void
    {
        $locale = $this->resolve();
        App::setLocale($locale);
    }

    /**
     * Switch locale (stores in session).
     */
    public function switch(string $locale): bool
    {
        if (!$this->isSupported($locale)) {
            return false;
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        return true;
    }

    public function supported(): array
    {
        return config('lang.supported', ['fr', 'en']);
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supported(), true);
    }

    public function labels(): array
    {
        return config('lang.labels', []);
    }

    public function current(): string
    {
        return App::getLocale();
    }

    public function label(string $locale): string
    {
        return config("lang.labels.{$locale}", $locale);
    }
}
