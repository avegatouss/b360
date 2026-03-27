<?php

namespace Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Bridges DB-stored settings (via setting() helper) into Laravel config()
 * so that mail, reCAPTCHA, app name, timezone, etc. respect admin-panel values.
 *
 * Registered by SettingsServiceProvider; runs on every request after the
 * Settings module is loaded.
 */
final class SettingsBootstrapProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Only apply if settings table exists and is accessible
        try {
            // -------------------------------------------------------
            // Email / SMTP — override Laravel mail config with DB settings
            // -------------------------------------------------------
            $smtpHost = setting('email.smtp_host');
            if ($smtpHost) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => (int) setting('email.smtp_port', 587),
                    'mail.mailers.smtp.username' => setting('email.smtp_username'),
                    'mail.mailers.smtp.password' => setting('email.smtp_password'),
                    'mail.mailers.smtp.encryption' => setting('email.smtp_encryption', 'tls'),
                    'mail.from.address' => setting('email.mail_from_address', config('mail.from.address')),
                    'mail.from.name' => setting('email.mail_from_name', config('mail.from.name')),
                ]);
            }

            // -------------------------------------------------------
            // reCAPTCHA — override config with DB settings
            // -------------------------------------------------------
            $recaptchaEnabled = setting('security.recaptcha_enabled');
            if ($recaptchaEnabled !== null) {
                config([
                    'recaptcha.enabled' => (bool) $recaptchaEnabled,
                    'recaptcha.site_key' => setting('security.recaptcha_site_key', config('recaptcha.site_key')),
                    'recaptcha.secret_key' => setting('security.recaptcha_secret_key', config('recaptcha.secret_key')),
                ]);
            }

            // -------------------------------------------------------
            // App name
            // -------------------------------------------------------
            $appName = setting('general.app_name');
            if ($appName) {
                config(['app.name' => $appName]);
            }

            // -------------------------------------------------------
            // Timezone
            // -------------------------------------------------------
            $tz = setting('company.timezone');
            if ($tz) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {
            // Fail silently (DB may not be ready, e.g., during migrations/install)
        }
    }
}
