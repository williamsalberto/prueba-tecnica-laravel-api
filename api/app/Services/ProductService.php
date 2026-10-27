<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{

    //Obtenemos lista de productos con sus relaciones.
    public function getAllProducts(): LengthAwarePaginator
    {
        return Product::with(['baseCurrency', 'prices.currency'])->paginate(15);
    }

    //Creamos un producto de manera transaccional 
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            return $product->load(['baseCurrency', 'prices.currency']);
        });
    }

    //Buscamos un producto por ID con sus relaciones.
    public function getProductById(int $id): Product
    {
        return Product::with(['baseCurrency', 'prices.currency'])->findOrFail($id);
    }

    //Actualizamos un producto ya creado
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);
            return $product->load(['baseCurrency', 'prices.currency']);
        });
    }

    //Eliminamos un producto ya creado
    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            return $product->delete();
        });
    }

    // Obtenemos los precios registrados en divisas secundarias para un producto
    public function getProductPrices(Product $product)
    {
        return $product->prices()->with('currency')->get();
    }

    // Registramos un precio en divisa secundaria para un producto
    public function addPriceToProduct(Product $product, array $data): \App\Models\ProductPrice
    {
        if ($product->currency_id == $data['currency_id']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'currency_id' => 'No se puede registrar un precio secundario con la misma divisa base del producto.',
            ]);
        }

        return DB::transaction(function () use ($product, $data) {
            $productPrice = $product->prices()->updateOrCreate(
                ['currency_id' => $data['currency_id']],
                ['price' => $data['price']]
            );

            return $productPrice->load('currency');
        });
    }
}
    