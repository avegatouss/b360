<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required','string','max:190'],
            'username' => ['nullable','string','max:100','unique:system.users,username'],
            'email' => ['required','email','max:190','unique:system.users,email'],
            'password' => ['required','string','min:8','max:255','confirmed'],
            'password_confirmation' => ['required','string'],
        ];
    }
}
