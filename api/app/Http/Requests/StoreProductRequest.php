<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
   //Determina si el usuario está autorizado para hacer esta petición.
    public function authorize(): bool
    {
        return true; // Permitimos la request para fines de la prueba 
    }

    //Reglas de validacion para la creación del producto.
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], // Nombre del producto
            'description' => ['nullable', 'string', 'max:5000'], // Descripción del producto
            'price' => ['required', 'numeric', 'min:0'], // Precio en la divisa base
            'currency_id' => ['required', 'integer', 'exists:currencies,id'], // Identificador de la divisa base
            'tax_cost' => ['sometimes', 'numeric', 'min:0'], // Costo de impuestos
            'manufacturing_cost' => ['sometimes', 'numeric', 'min:0'], // Costo de fabricación
        ];
    }

    //Mensajes personalizados opcionales para mayor claridad en las respuestas de error.
    public function messages(): array
    {
        return [
            'currency_id.exists' => 'La divisa base especificada no existe en el sistema.',
            'price.required' => 'El precio del producto es obligatorio.',
        ];
    }
}