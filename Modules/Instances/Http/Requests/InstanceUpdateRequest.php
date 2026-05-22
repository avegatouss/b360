<?php

namespace Modules\Instances\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InstanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'domain' => ['nullable', 'string', 'max:190'],
            'subdomain' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}
