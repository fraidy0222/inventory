<?php

namespace App\Http\Requests\InventarioTienda;

use Illuminate\Foundation\Http\FormRequest;

class InventarioTiendaRequest extends FormRequest
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
            'cantidad' => 'required|numeric',
            'cantidad_minima' => 'required|numeric',
            'cantidad_maxima' => 'required|numeric',
            'producto_id' => 'required|exists:productos,id',
            'tienda_id' => 'required|exists:tiendas,id',
        ];
    }

    public function messages()
    {
        return [
            'cantidad.required' => 'El campo cantidad es obligatorio.',
            'cantidad_minima.required' => 'El campo cantidad minima es obligatorio.',
            'cantidad_maxima.required' => 'El campo cantidad maxima es obligatorio.',
            'producto_id.required' => 'El campo producto es obligatorio.',
            'tienda_id.required' => 'El campo tienda es obligatorio.',
        ];
    }
}
