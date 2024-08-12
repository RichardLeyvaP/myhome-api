<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email', 'max:250'],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'max:250'],
            'role' => ['required', new Enum(RoleEnum::class)],
            'status' => ['required', new Enum(StatusEnum::class)]
        ];
    }
}
