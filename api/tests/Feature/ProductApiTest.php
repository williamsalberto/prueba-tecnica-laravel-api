<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected Currency $usd;
    protected Currency $eur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);

        $this->usd = Currency::where('symbol', 'USD')->first();
        $this->eur = Currency::where('symbol', 'EUR')->first();
    }

    /**
     * 1. GET /api/products
     * Debe obtener la lista paginada de productos con estructura JSON.
     */
    public function test_can_get_list_of_products(): void
    {
        Product::create([
            'name' => 'Producto 1',
            'description' => 'Descripción 1',
            'price' => 100.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 10.00,
            'manufacturing_cost' => 50.00,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'currency' => ['id', 'name', 'symbol', 'exchange_rate'],
                        'tax_cost',
                        'manufacturing_cost',
                        'prices',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * 2. POST /api/products
     * Debe permitir crear un nuevo producto con datos válidos.
     */
    public function test_can_create_a_product_successfully(): void
    {
        $payload = [
            'name' => 'Monitor 4K',
            'description' => 'Monitor gamer de 27 pulgadas',
            'price' => 350.50,
            'currency_id' => $this->usd->id,
            'tax_cost' => 50.00,
            'manufacturing_cost' => 200.00,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Monitor 4K')
            ->assertJsonPath('data.price', '350.50')
            ->assertJsonPath('data.currency.symbol', 'USD')
            ->assertJsonPath('message', 'Producto creado exitosamente');

        $this->assertDatabaseHas('products', [
            'name' => 'Monitor 4K',
            'currency_id' => $this->usd->id,
        ]);
    }

    /**
     * POST /api/products (Validación de errores)
     * Debe retornar 422 si los datos requeridos no son enviados o son inválidos.
     */
    public function test_cannot_create_product_with_invalid_data(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => '',
            'price' => -10, // precio negativo
            'currency_id' => 9999, // divisa inexistente
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'currency_id']);
    }

    /**
     * 3. GET /api/products/{id}
     * Debe obtener los detalles de un producto existente por su ID.
     */
    public function test_can_get_single_product_by_id(): void
    {
        $product = Product::create([
            'name' => 'Teclado Mecánico',
            'description' => 'Switches red silenciosos',
            'price' => 89.99,
            'currency_id' => $this->usd->id,
            'tax_cost' => 15.00,
            'manufacturing_cost' => 40.00,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Teclado Mecánico')
            ->assertJsonPath('data.currency.symbol', 'USD');
    }

    /**
     * GET /api/products/{id} (404)
     * Debe retornar 404 si el producto no existe.
     */
    public function test_returns_404_when_product_not_found(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404);
    }

    /**
     * 4. PUT /api/products/{id}
     * Debe actualizar los datos de un producto existente.
     */
    public function test_can_update_an_existing_product(): void
    {
        $product = Product::create([
            'name' => 'Mouse USB',
            'description' => 'Mouse óptico básico',
            'price' => 15.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 2.00,
            'manufacturing_cost' => 8.00,
        ]);

        $updatePayload = [
            'name' => 'Mouse Inalámbrico RGB',
            'price' => 29.99,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Mouse Inalámbrico RGB')
            ->assertJsonPath('data.price', '29.99')
            ->assertJsonPath('message', 'Producto actualizado exitosamente');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Mouse Inalámbrico RGB',
        ]);
    }

    /**
     * 5. DELETE /api/products/{id}
     * Debe eliminar un producto y sus relaciones asociadas.
     */
    public function test_can_delete_a_product(): void
    {
        $product = Product::create([
            'name' => 'Disco Duro Externo',
            'description' => '1TB USB 3.0',
            'price' => 60.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 10.00,
            'manufacturing_cost' => 30.00,
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Producto eliminado exitosamente');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * 6. POST /api/products/{id}/prices
     * Debe registrar un precio en una divisa diferente para el producto.
     */
    public function test_can_create_a_new_price_for_a_product(): void
    {
        $product = Product::create([
            'name' => 'Smartphone Pro',
            'description' => 'Teléfono gama alta',
            'price' => 999.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 150.00,
            'manufacturing_cost' => 500.00,
        ]);

        $pricePayload = [
            'currency_id' => $this->eur->id,
            'price' => 919.00,
        ];

        $response = $this->postJson("/api/products/{$product->id}/prices", $pricePayload);

        $response->assertStatus(201)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.price', '919.00')
            ->assertJsonPath('data.currency.symbol', 'EUR')
            ->assertJsonPath('message', 'Precio registrado exitosamente');

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $this->eur->id,
        ]);
    }

    /**
     * POST /api/products/{id}/prices (Regla de negocio: no duplicar divisa)
     * Debe retornar error 422 si se intenta registrar la misma divisa dos veces.
     */
    public function test_cannot_register_duplicate_price_for_same_currency(): void
    {
        $product = Product::create([
            'name' => 'Tablet 10 pulg',
            'description' => 'Tablet Android',
            'price' => 200.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 30.00,
            'manufacturing_cost' => 120.00,
        ]);

        $product->prices()->create([
            'currency_id' => $this->eur->id,
            'price' => 184.00,
        ]);

        // Intentar registrar de nuevo EUR
        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $this->eur->id,
            'price' => 180.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * POST /api/products/{id}/prices (Regla de negocio: no usar la divisa base como secundaria)
     */
    public function test_cannot_register_base_currency_as_secondary_price(): void
    {
        $product = Product::create([
            'name' => 'Auriculares',
            'description' => 'Bluetooth',
            'price' => 50.00,
            'currency_id' => $this->usd->id,
            'tax_cost' => 5.00,
            'manufacturing_cost' => 20.00,
        ]);

        // Intentar registrar USD cuando la divisa base ya es USD
        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $this->usd->id,
            'price' => 50.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * 7. GET /api/products/{id}/prices
     * Debe obtener la lista de precios registrados para el producto.
     */
    public function test_can_get_list_of_prices_for_a_product(): void
    {
        $product = Product::create([
            'name' => 'Consola de Juegos',
            'description' => 'Next gen console',
            'price' => 499.99,
            'currency_id' => $this->usd->id,
            'tax_cost' => 70.00,
            'manufacturing_cost' => 350.00,
        ]);

        $product->prices()->create([
            'currency_id' => $this->eur->id,
            'price' => 460.00,
        ]);

        $response = $this->getJson("/api/products/{$product->id}/prices");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'currency' => ['id', 'name', 'symbol', 'exchange_rate'],
                        'price',
                        'created_at',
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.currency.symbol', 'EUR');
    }
}
