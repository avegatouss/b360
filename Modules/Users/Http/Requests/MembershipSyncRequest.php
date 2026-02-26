<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MembershipSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'memberships' => ['required','array','min:1'],
            'memberships.*.instance_id' => ['required','integer','min:1'],
            'memberships.*.status' => ['required','in:active,invited,disabled'],
            'memberships.*.role' => ['nullable','string','max:100'],
        ];
    }
}
