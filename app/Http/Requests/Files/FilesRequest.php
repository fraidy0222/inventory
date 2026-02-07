<?php

namespace App\Http\Requests\Files;

use Illuminate\Foundation\Http\FormRequest;

class FilesRequest extends FormRequest
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
            'archivo' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
                'max:4096'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es requerido',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre debe tener como máximo 255 caracteres',
            'archivo.required' => 'El archivo es requerido',
            'archivo.file' => 'El archivo debe ser un archivo',
            'archivo.mimes' => 'El archivo debe ser un archivo de tipo pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif',
            'archivo.max' => 'El archivo debe tener como máximo 4096 kilobytes',
        ];
    }
}
