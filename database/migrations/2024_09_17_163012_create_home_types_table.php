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
        Schema::create('home_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Tipo de hogar (ej. casa, apartamento)
            $table->text('description')->nullable();  // Descripción opcional
            $table->string('icon')->nullable();  // Campo para el icono del tipo de hogar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_types');
    }
};
