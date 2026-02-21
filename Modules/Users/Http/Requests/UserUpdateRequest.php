<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        $userId = (int) $this->route('user')->id;

        return [
            'name' => ['required','string','max:190'],
            'email' => ['required','email','max:190',"unique:system.users,email,{$userId}"],
            'password' => ['nullable','string','min:8','max:255'],
        ];
    }
}
