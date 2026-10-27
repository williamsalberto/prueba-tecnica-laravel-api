<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * Listado paginado de productos con sus relaciones de divisas y precios.
     */
    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getAllProducts();
        return ProductResource::collection($products);
    }

    /**
     * Creación de un nuevo producto con divisa base.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());

        return (new ProductResource($product))
            ->additional(['message' => 'Producto creado exitosamente'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Obtener el detalle de un producto específico.
     */
    public function show(Product $product): ProductResource
    {
        $product->loadMissing(['baseCurrency', 'prices.currency']);
        return new ProductResource($product);
    }

    /**
     * Actualización de un producto existente.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return (new ProductResource($updatedProduct))->additional([
            'message' => 'Producto actualizado exitosamente'
        ]);
    }

    /**
     * Eliminación de un producto y sus precios asociados en cascada.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ], Response::HTTP_OK);
    }
}