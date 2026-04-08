<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('roles', 'name')
                    ->where('tenant_id', getPermissionsTeamId()),
            ],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::in(PermissionEnum::all())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Role name must be lowercase letters and underscores only.',
        ];
    }
}
