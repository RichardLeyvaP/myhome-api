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
        Schema::create('category_person', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id'); // Clave foránea a la tabla categories
            $table->unsignedBigInteger('person_id'); // Clave foránea a la tabla people
            
            // Definir las claves foráneas
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_person');
    }
};
