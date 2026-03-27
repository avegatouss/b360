<?php

namespace Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

final class RecaptchaV3 implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if reCAPTCHA is disabled
        if (! config('recaptcha.enabled')) {
            return;
        }

        if (empty($value)) {
            $fail('reCAPTCHA verification failed. Please try again.');
            return;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => config('recaptcha.secret_key'),
                'response' => $value,
            ]);

            $body = $response->json();

            if (
                ! ($body['success'] ?? false)
                || ($body['score'] ?? 0) < config('recaptcha.threshold', 0.5)
            ) {
                $fail('reCAPTCHA verification failed. Please try again.');
            }
        } catch (\Throwable $e) {
            // If the verification service is unreachable, fail closed
            $fail('reCAPTCHA verification failed. Please try again.');
        }
    }
}
