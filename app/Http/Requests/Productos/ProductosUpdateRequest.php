<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;

class ProductosUpdateRequest extends FormRequest
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
            'nombre' => 'required|string|max:150|unique:productos,nombre,' . $this->route('producto')->id,
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre debe tener un máximo de 150 caracteres',
            'nombre.unique' => 'El nombre ya existe',
            'categoria.max' => 'La categoría debe tener un máximo de 100 caracteres',
        ];
    }
}
