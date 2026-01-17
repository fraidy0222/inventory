<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;

class ProductosStoreRequest extends FormRequest
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
            'nombre' => 'required|string|max:150|unique:productos,nombre',
            'descripcion' => 'nullable|string',
            'categoria' => 'nullable|string|max:100',
            'costo_promedio' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
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
            'costo_promedio.numeric' => 'El costo promedio debe ser un número',
            'costo_promedio.min' => 'El costo promedio no puede ser negativo',
            'precio_venta.numeric' => 'El precio de venta debe ser un número',
            'precio_venta.min' => 'El precio de venta no puede ser negativo',
        ];
    }
}
