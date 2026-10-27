<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : $product;

        return [
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
                function ($attribute, $value, $fail) use ($product) {
                    if ($product instanceof Product && $product->currency_id == $value) {
                        $fail('No se puede asignar como precio secundario la misma divisa base del producto.');
                    }
                },
                Rule::unique('product_prices', 'currency_id')->where(function ($query) use ($productId) {
                    return $query->where('product_id', $productId);
                }),
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.required' => 'La divisa es obligatoria.',
            'currency_id.integer' => 'El identificador de la divisa debe ser un número entero.',
            'currency_id.exists' => 'La divisa seleccionada no existe en el sistema.',
            'currency_id.unique' => 'Ya existe un precio registrado para este producto en la divisa especificada.',
            'price.required' => 'El precio del producto es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}