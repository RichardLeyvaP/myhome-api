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
        Schema::create('people', function (Blueprint $table) {
            $table->id(); // ID de la persona (Primary Key)
            $table->unsignedBigInteger('user_id'); // Clave foránea que apunta a la tabla users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Relación con users
            $table->string('name'); // Nombre completo
            $table->date('birth_date')->nullable(); // Fecha de nacimiento
            $table->integer('age')->nullable(); // Campo de edad (opcional) para almacenar la edad
            $table->string('gender')->nullable(); // Género
            $table->string('email')->nullable(); // Correo electrónico (opcional)
            $table->string('phone')->nullable(); // Número de teléfono (opcional)
            $table->text('address')->nullable(); // Dirección (opcional)
            $table->string('image')->nullable(); // Campo de imagen (opcional)
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
