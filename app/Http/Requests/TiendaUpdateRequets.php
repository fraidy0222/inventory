<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TiendaUpdateRequets extends FormRequest
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
            'nombre' => 'required|string|max:255|unique:tiendas,nombre,' . $this->route('tienda')->id,
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre debe tener un máximo de 255 caracteres',
            'nombre.unique' => 'El nombre ya existe',
            'is_active.boolean' => 'El estado debe ser un booleano',
        ];
    }
}
