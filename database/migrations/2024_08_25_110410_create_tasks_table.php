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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedBigInteger('priority_id'); // Foreign key to priorities table
            $table->unsignedBigInteger('parent_id')->nullable(); // Sub tareas
            $table->unsignedBigInteger('status_id'); // Foreign key to status table
            $table->unsignedBigInteger('category_id'); // Foreign key to categories table
            $table->string('recurrence')->nullable();
            $table->integer('estimated_time')->nullable(); // in minutes
            $table->text('comments')->nullable();
            $table->string('attachments')->nullable(); // Archivo o imagen a guardar
            $table->string('geo_location')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('priority_id')->references('id')->on('priorities')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
