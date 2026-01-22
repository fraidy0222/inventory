<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventarioNoticiaRequest extends FormRequest
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
            'producto_id' => 'required',
            'tienda_id' => 'required',
            'cantidad' => 'required',
            'precio_venta' => 'required',
            'costo_promedio' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'El producto es requerido',
            'tienda_id.required' => 'La tienda es requerida',
            'cantidad.required' => 'La cantidad es requerida',
            'precio_venta.required' => 'El precio de venta es requerido',
            'costo_promedio.required' => 'El costo promedio es requerido',
        ];
    }
}
