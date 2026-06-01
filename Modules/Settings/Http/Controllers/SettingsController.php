<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Hooks\HookFilter;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Core\Support\CurrentInstance;
use Modules\Settings\Services\SettingsManager;

final class SettingsController extends Controller
{
    /**
     * Keys whose values are sensitive and stored encrypted.
     */
    private const ENCRYPTED_KEYS = [
        'email.smtp_password',
        'security.recaptcha_secret_key',
    ];

    /**
     * Password/secret fields: if the submitted value is empty the existing
     * value is kept (never overwritten with blank).
     */
    private const PASSWORD_FIELDS = [
        'email.smtp_password',
        'security.recaptcha_secret_key',
    ];

    public function index(string $slug, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);
        $groups = $filter->filter($registry->settingsGroups(), $user, $instance);

        // Redirect to first group if available
        if ($groups->isNotEmpty()) {
            return redirect()->route('settings.group', [$instance->slug, $groups->first()->id]);
        }

        return view('settings::index', compact('instance', 'groups'));
    }

    public function group(string $slug, string $group, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $user = auth()->user();

        $registry = app(HookRegistry::class);
        $filter = app(HookFilter::class);
        $groups = $filter->filter($registry->settingsGroups(), $user, $instance);

        $currentGroup = $groups->firstWhere('id', $group);
        if (!$currentGroup) {
            abort(404, 'Groupe de parametres introuvable.');
        }

        $scopeId = $this->settingsScope($instance);

        // Instance settings merged over global (cascade)
        $globalValues = $settings->group($group, 0);
        $instanceValues = $scopeId > 0 ? $settings->group($group, $scopeId) : [];
        $values = array_merge($globalValues, $instanceValues);

        // Decrypt encrypted values for display (masked in the view)
        foreach (self::ENCRYPTED_KEYS as $fullKey) {
            [$g, $k] = explode('.', $fullKey, 2);
            if ($g === $group && isset($values[$k]) && $values[$k] !== '') {
                try {
                    $values[$k] = Crypt::decryptString($values[$k]);
                } catch (\Throwable) {
                    // Value may not be encrypted yet (legacy) — keep as-is
                }
            }
        }

        return view('settings::index', compact('instance', 'groups', 'currentGroup', 'values', 'scopeId'));
    }

    public function updateGroup(Request $request, string $slug, string $group, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->settingsScope($instance);

        $settingsData = $request->input('settings', []);
        $types = $request->input('types', []);

        foreach ($settingsData as $key => $value) {
            $fullKey = "{$group}.{$key}";
            $type = $types[$key] ?? 'string';

            // Skip blank password fields (keep existing value)
            if (in_array($fullKey, self::PASSWORD_FIELDS, true) && ($value === '' || $value === null)) {
                continue;
            }

            // Encrypt sensitive values before storage
            if (in_array($fullKey, self::ENCRYPTED_KEYS, true) && $value !== '' && $value !== null) {
                $value = Crypt::encryptString($value);
            }

            // Handle JSON arrays (e.g. payment_methods checkboxes)
            if ($type === 'json' && is_array($value)) {
                $value = json_encode($value);
                $type = 'json';
            }

            $settings->set($fullKey, $value, $scopeId, $type);
        }

        // Handle file uploads (e.g., branding images)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $key => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store("branding/{$group}", 'public');
                    $settings->set("{$group}.{$key}", $path, $scopeId, 'string');
                }
            }
        }

        return redirect()
            ->route('settings.group', [$instance->slug, $group])
            ->with('status', 'Parametres mis a jour.');
    }

    /**
     * Send a test email using current SMTP settings.
     */
    public function testEmail(string $slug, SettingsManager $settings)
    {
        $instance = CurrentInstance::get();
        $scopeId = $this->settingsScope($instance);
        $user = auth()->user();

        if (!$user || !$user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune adresse email associee a votre compte.',
            ]);
        }

        // Read SMTP settings
        $smtpHost = $settings->get('email.smtp_host', '', $scopeId);
        $smtpPort = (int) $settings->get('email.smtp_port', 587, $scopeId);
        $smtpUsername = $settings->get('email.smtp_username', '', $scopeId);
        $smtpEncryption = $settings->get('email.smtp_encryption', 'tls', $scopeId);
        $fromAddress = $settings->get('email.mail_from_address', config('mail.from.address'), $scopeId);
        $fromName = $settings->get('email.mail_from_name', config('mail.from.name'), $scopeId);

        // Decrypt password
        $smtpPassword = $settings->get('email.smtp_password', '', $scopeId);
        if ($smtpPassword) {
            try {
                $smtpPassword = Crypt::decryptString($smtpPassword);
            } catch (\Throwable) {
                // legacy unencrypted value
            }
        }

        if (!$smtpHost) {
            return response()->json([
                'success' => false,
                'message' => 'Le serveur SMTP n\'est pas configure.',
            ]);
        }

        try {
            config([
                'mail.mailers.settings_test' => [
                    'transport' => 'smtp',
                    'host' => $smtpHost,
                    'port' => $smtpPort,
                    'username' => $smtpUsername,
                    'password' => $smtpPassword,
                    'encryption' => $smtpEncryption ?: null,
                ],
            ]);

            Mail::mailer('settings_test')->raw(
                'Ceci est un email de test envoye depuis B360 pour verifier la configuration SMTP.',
                function ($message) use ($user, $fromAddress, $fromName) {
                    $message->to($user->email)
                        ->from($fromAddress ?: 'test@b360.app', $fromName ?: 'B360')
                        ->subject('B360 — Test de configuration SMTP');
                }
            );

            return response()->json([
                'success' => true,
                'message' => "Email de test envoye a {$user->email}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Echec : ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine settings scope: root = 0 (global), non-root = instance_id.
     * Root settings affect all instances. Instance settings only affect that instance.
     */
    private function settingsScope($instance): int
    {
        return $instance->isRoot() ? 0 : (int) $instance->id;
    }
}
