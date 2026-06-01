<?php

namespace Modules\Eshop360\Services\Sms;

interface SmsDriverInterface
{
    /**
     * Send an SMS message.
     */
    public function send(string $to, string $message): bool;

    /**
     * Get the account balance (if supported by the provider).
     */
    public function getBalance(): ?float;

    /**
     * Return the configuration field definitions required by this driver.
     *
     * Each entry should be: ['name' => ..., 'label' => ..., 'type' => 'text'|'password'|'select'|'textarea', 'required' => bool]
     */
    public static function getConfigFields(): array;
}
