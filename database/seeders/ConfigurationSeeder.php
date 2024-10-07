<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration;
use Carbon\Carbon;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['isDefault' => true],  // Criterio para buscar la configuración por defecto existente
            [
                'appName' => 'Huoon',  // Datos nuevos o actualizados
                'appVersion' => '1.0.0',
                'language' => 'es',
                'defaultCurrency' => 'USD',
                'themeColor' => '#FFFFFF',
                'backgroundColor' => '#F0F0F0',
                'textColor' => '#000000',
                'buttonColor' => '#007BFF',
                'isDarkModeEnabled' => true,
                'notificationsEnabled' => true,
                'apiEndpoint' => 'https://api.miapp.com',
                'connectionTimeout' => 30,
                'retryAttempts' => 3,
                'useBiometricAuth' => true,
                'requirePinForSensitiveActions' => true,
                'storagePath' => '/data/app',
                'maxCacheSize' => 1024,
                'autoUpdateEnabled' => true,
                'supportContactEmail' => 'soporte@miapp.com',
                'lastSyncTime' => Carbon::now(), // Conversión de fecha,
                'fontSize' => 14,
                'isDefault' => true,  // Mantenemos la configuración como la predeterminada
            ]
        );
    }
}
