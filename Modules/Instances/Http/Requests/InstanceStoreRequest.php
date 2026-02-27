<?php

namespace Modules\Instances\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InstanceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9][a-z0-9\-]*$/', 'unique:system.instances,slug'],
            'domain' => ['nullable', 'string', 'max:190'],
            'subdomain' => ['nullable', 'string', 'max:100'],
            'db_mode' => ['required', 'in:shared,dedicated'],
            'database' => ['required_if:db_mode,dedicated', 'nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.',
            'database.regex' => 'Le nom de base ne peut contenir que des lettres, chiffres et underscores.',
        ];
    }
}
