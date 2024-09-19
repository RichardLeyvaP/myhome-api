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
        Schema::create('homes', function (Blueprint $table) {
            $table->id(); // ID del hogar (Primary Key)
            $table->string('name')->nullable(); // Nombre del hogar (puede ser nulo)
            $table->string('address'); // Dirección del hogar
            $table->unsignedBigInteger('home_type_id'); // Relación con product_statuses
            $table->integer('residents')->nullable(); // Número de residentes (default 1)
            $table->string('geolocation')->nullable(); // Ubicación geográfica (puede ser nulo)
            $table->string('timezone')->nullable(); // Zona horaria
            $table->string('status')->default('Activa'); // Estado del hogar
            $table->string('image')->nullable(); // Archivo o imagen a guardar
            $table->timestamps(); // Incluye created_at y updated_at

            $table->foreign('home_type_id')->references('id')->on('home_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homes');
    }
};
