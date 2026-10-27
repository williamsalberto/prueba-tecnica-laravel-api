<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // Identificador único del precio
            'product_id' => $this->product_id, // Identificador del producto
            'currency' => new CurrencyResource($this->whenLoaded('currency')), // Datos de la divisa relacionada
            'price' => $this->price, // Precio en la divisa especificada
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}