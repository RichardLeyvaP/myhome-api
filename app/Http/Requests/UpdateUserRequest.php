<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $id = optional($user)->id;

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:250', "unique:users,email,{$id},id"],
            'password' => [Rule::excludeIf(!$this->change_password), 'required', 'string', 'confirmed', 'min:8', 'max:250'],
            'role' => ['required', new Enum(RoleEnum::class)],
            'status' => ['required', new Enum(StatusEnum::class)]
        ];
    }
}
