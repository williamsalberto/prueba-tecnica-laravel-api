<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Identificador del producto
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict'); // Identificador de la divisa
            $table->decimal('price', 12, 2); // Precio del producto en la divisa correspondiente
            $table->timestamps();

            //La siguiente linea garantiza que un producto no tenga dos precios con la misma divisa
            $table->unique(['product_id', 'currency_id']);
            
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
