<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del producto
            $table->unsignedBigInteger('category_id'); // Relación con product_categories
            $table->unsignedBigInteger('status_id'); // Relación con product_statuses
            $table->integer('quantity'); // Cantidad
            $table->decimal('unit_price', 16, 2); // Precio por unidad
            $table->decimal('total_price', 16, 2); // Precio total
            $table->timestamp('purchase_date')->nullable(); // Fecha de compra
            $table->string('purchase_place')->nullable(); // Lugar de compra
            $table->date('expiration_date')->nullable(); // Fecha de caducidad
            $table->string('brand')->nullable(); // Marca
            $table->text('additional_notes')->nullable(); // Notas adicionales
            $table->string('image')->nullable(); // Archivo o imagen a guardar
            $table->timestamps(); // Created_at y updated_at

            // Definir las claves foráneas
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
            // Puedes agregar la relación con la tabla de hogares si existe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
