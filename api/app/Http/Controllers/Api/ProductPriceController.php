<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductPriceController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * Obtener la lista de precios registrados en otras divisas para un producto específico.
     */
    public function index(Product $product): AnonymousResourceCollection
    {
        $prices = $this->productService->getProductPrices($product);
        return ProductPriceResource::collection($prices);
    }

    /**
     * Registrar un nuevo precio en una divisa específica para un producto.
     */
    public function store(StoreProductPriceRequest $request, Product $product): JsonResponse
    {
        $productPrice = $this->productService->addPriceToProduct($product, $request->validated());

        return (new ProductPriceResource($productPrice))
            ->additional(['message' => 'Precio registrado exitosamente'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
