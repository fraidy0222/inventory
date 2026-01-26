<?php

namespace App\Http\Requests\Destino;

use Illuminate\Foundation\Http\FormRequest;

class DestinoRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de caracteres',
            'nombre.max' => 'El nombre debe tener un máximo de 255 caracteres',
        ];
    }
}
