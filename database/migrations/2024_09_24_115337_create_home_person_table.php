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
        Schema::create('home_person', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id'); // Clave foránea a la tabla homes
            $table->unsignedBigInteger('person_id'); // Clave foránea a la tabla people
            $table->unsignedBigInteger('role_id'); // Clave foránea a la tabla roles (nuevo atributo)
            
            // Definir las claves foráneas
            $table->foreign('home_id')->references('id')->on('homes')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade'); // Relación con roles

            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_person');
    }
};
