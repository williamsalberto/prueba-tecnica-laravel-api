<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // Identificador único de la divisa
            'symbol' => $this->symbol, // Símbolo de la divisa
            'exchange_rate' => $this->exchange_rate, // Tasa de cambio
        ];
    }
}