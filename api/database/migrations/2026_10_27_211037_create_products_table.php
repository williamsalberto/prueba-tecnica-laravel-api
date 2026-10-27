<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del producto
            $table->string('description')->nullable(); // Descripcion del producto
            $table->decimal('price',12,2); //Precio del producto
            $table->foreignId('currency_id')->constrained('currencies')->OnDelete('restrict'); //FK de id de divisa
            $table->decimal('tax_cost', 12, 2)->default(0); //Costo del impuesto
            $table->decimal('manufacturing_cost', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
