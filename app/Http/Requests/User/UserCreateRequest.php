<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required|string|min:8|max:255|confirmed',
            'role' => 'required|string|in:empleado,admin,supervisor',
            'is_active' => 'nullable|boolean',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'email.required' => 'El correo electrónico es requerido',
            'email.unique' => 'El correo electrónico ya está en uso',
            'email.max' => 'El correo no puede tener más de 150 caracteres',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.max' => 'La contraseña no puede tener más de 255 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'role.required' => 'El rol es requerido',
            'role.in' => 'El rol no es válido',
            'is_active.boolean' => 'El estado debe ser un booleano',
        ];
    }
}
