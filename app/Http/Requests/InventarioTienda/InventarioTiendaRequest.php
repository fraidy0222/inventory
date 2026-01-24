<?php

namespace App\Http\Requests\InventarioTienda;

use App\Models\InventarioTienda;
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
        $tiendaId = $this->tienda_id;
        $productoId = $this->producto_id;

        return [
            'tienda_id' => 'required|exists:tiendas,id',
            'producto_id' => [
                'required',
                'exists:productos,id',
                // Validar que no exista ya esta combinación
                function ($attribute, $value, $fail) use ($tiendaId, $productoId) {
                    $query = InventarioTienda::where('tienda_id', $tiendaId)
                        ->where('producto_id', $productoId);

                    // Si estamos editando, excluir el registro actual
                    if ($this->route('inventarioTienda')) {
                        $query->where('id', '!=', $this->route('inventarioTienda')->id);
                    }

                    if ($query->exists()) {
                        $fail('Este producto ya está registrado en la tienda seleccionada.');
                    }
                },
            ],
            'cantidad' => 'required|numeric|min:0',
            'cantidad_minima' => 'nullable|numeric|min:0',
            'cantidad_maxima' => 'nullable|numeric|min:0|gte:cantidad_minima',
        ];
    }

    public function messages()
    {
        return [
            'tienda_id.required' => 'Debe seleccionar una tienda.',
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'La cantidad es requerida.',
            'cantidad.numeric' => 'La cantidad debe ser un número.',
            'cantidad_minima.numeric' => 'La cantidad mínima debe ser un número.',
            'cantidad_maxima.numeric' => 'La cantidad máxima debe ser un número.',
            'cantidad_maxima.gte' => 'La cantidad máxima debe ser mayor o igual que la mínima.',
        ];
    }
}
