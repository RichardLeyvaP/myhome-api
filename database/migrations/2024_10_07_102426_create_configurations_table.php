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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            // El campo user_id es nullable para permitir configuraciones del sistema sin un usuario asociado
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('appName')->nullable();
            $table->string('appVersion')->nullable();
            $table->string('language')->nullable();
            $table->string('defaultCurrency')->nullable();
            $table->string('themeColor')->nullable();
            $table->string('backgroundColor')->nullable();
            $table->string('textColor')->nullable();
            $table->string('buttonColor')->nullable();
            $table->boolean('isDarkModeEnabled')->nullable();
            $table->boolean('notificationsEnabled')->nullable();
            $table->string('apiEndpoint')->nullable();
            $table->integer('connectionTimeout')->nullable();
            $table->integer('retryAttempts')->nullable();
            $table->boolean('useBiometricAuth')->nullable();
            $table->boolean('requirePinForSensitiveActions')->nullable();
            $table->string('storagePath')->nullable();
            $table->integer('maxCacheSize')->nullable();
            $table->boolean('autoUpdateEnabled')->nullable();
            $table->string('supportContactEmail')->nullable();
            $table->timestamp('lastSyncTime')->nullable();
            $table->integer('fontSize')->nullable();
            $table->boolean('isDefault')->default(false); // Indica si es la configuración por defecto del sistema
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
