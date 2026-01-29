<?php

namespace App\Http\Requests\Movimientos;

use App\Models\InventarioTienda;
use Illuminate\Foundation\Http\FormRequest;

class MovimientoUpdateRequest extends FormRequest
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
            'inventario_tienda_id' => [
                'required',
                'exists:inventario_tiendas,id',
                // Validar que traslados + venta_diaria no excedan la cantidad disponible
                function ($attribute, $value, $fail) {
                    $inventario = InventarioTienda::find($value);

                    if (!$inventario) {
                        $fail('El inventario de tienda no existe.');
                        return;
                    }

                    $traslados = $this->input('traslados', 0);
                    $ventaDiaria = $this->input('venta_diaria', 0);
                    $totalSalida = $traslados + $ventaDiaria;

                    if ($totalSalida > $inventario->cantidad) {
                        $fail("La suma de traslados ({$traslados}) y venta diaria ({$ventaDiaria}) no puede ser mayor que la cantidad disponible en inventario ({$inventario->cantidad}).");
                    }
                },
            ],
            'producto_id' => 'required|exists:productos,id',
            'destino_id' => 'required|exists:destinos,id',
            'usuario_id' => 'required|exists:users,id',
            'entradas' => 'nullable|numeric|min:0',
            'salidas' => 'nullable|numeric|min:0',
            'traslados' => 'nullable|numeric|min:0',
            'venta_diaria' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'inventario_tienda_id.required' => 'Debe seleccionar un inventario de tienda.',
            'inventario_tienda_id.exists' => 'El inventario de tienda seleccionado no existe.',
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'destino_id.required' => 'Debe seleccionar un destino.',
            'destino_id.exists' => 'El destino seleccionado no existe.',
            'usuario_id.required' => 'Debe seleccionar un usuario.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'entradas.numeric' => 'Las entradas deben ser un número.',
            'entradas.min' => 'Las entradas no pueden ser negativas.',
            'salidas.numeric' => 'Las salidas deben ser un número.',
            'salidas.min' => 'Las salidas no pueden ser negativas.',
            'traslados.numeric' => 'Los traslados deben ser un número.',
            'traslados.min' => 'Los traslados no pueden ser negativos.',
            'venta_diaria.numeric' => 'La venta diaria debe ser un número.',
            'venta_diaria.min' => 'La venta diaria no puede ser negativa.',
        ];
    }
}
