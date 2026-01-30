<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.manage') ?? false;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:roles,slug,' . $roleId],
            'description' => ['nullable', 'string', 'max:200'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:80'],
        ];
    }
}
