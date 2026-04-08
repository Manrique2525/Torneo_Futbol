<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        return [
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::in(PermissionEnum::all())],
        ];
    }
}
