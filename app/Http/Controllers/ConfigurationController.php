<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        Log::info(auth()->user()->name . '-' . "Visualiza la configuración de un usuario");

        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            // Intentar encontrar la configuración del usuario
            $config = Configuration::where('user_id', $user->id)->first();

            // Si no existe, obtener la configuración por defecto del sistema
            if (!$config) {
                $config = Configuration::where('isDefault', true)->first();
            }

            $userConfig []= [
                'userId' => $user->id,
                'appName' => $config->appName,
                'appVersion' => $config->appVersion,
                'language' => $config->language,
                'defaultCurrency' => $config->defaultCurrency,
                'themeColor' => $config->themeColor,
                'backgroundColor' => $config->backgroundColor,
                'textColor' => $config->textColor,
                'buttonColor' => $config->buttonColor,
                'isDarkModeEnabled' => (bool)$config->isDarkModeEnabled,
                'notificationsEnabled' => (bool)$config->notificationsEnabled,
                'apiEndpoint' => $config->apiEndpoint,
                'connectionTimeout' => $config->connectionTimeout,
                'retryAttempts' => $config->retryAttempts,
                'useBiometricAuth' => (bool)$config->useBiometricAuth,
                'requirePinForSensitiveActions' => (bool)$config->requirePinForSensitiveActions,
                'storagePath' => $config->storagePath,
                'maxCacheSize' => $config->maxCacheSize,
                'autoUpdateEnabled' => (bool)$config->autoUpdateEnabled,
                'supportContactEmail' => $config->supportContactEmail,
                'lastSyncTime' => $config->lastSyncTime, // Formato ISO 8601
                'fontSize' => $config->fontSize,
            ];


            return response()->json(['configurations' => $userConfig], 200);
        } catch (\Exception $e) {
            Log::info('ConfigurationkController->Show');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Edita la configuración de un usuario");
        
        try {
            // Extraer el array de datos desde la request
            $data = $request->all();

            // Validar solo los campos que puedan estar presentes
            $validator = Validator::make($data, [
                'appName' => 'sometimes|nullable|string|max:255',
                'appVersion' => 'sometimes|nullable|string|max:255',
                'language' => 'sometimes|nullable|string',
                'defaultCurrency' => 'sometimes|nullable|string',
                'themeColor' => 'sometimes|nullable|string',
                'backgroundColor' => 'sometimes|nullable|string',
                'textColor' => 'sometimes|nullable|string',
                'buttonColor' => 'sometimes|nullable|string',
                'isDarkModeEnabled' => 'sometimes|nullable|boolean',
                'notificationsEnabled' => 'sometimes|nullable|boolean',
                'apiEndpoint' => 'sometimes|nullable|url',
                'connectionTimeout' => 'sometimes|nullable|integer',
                'retryAttempts' => 'sometimes|nullable|integer',
                'useBiometricAuth' => 'sometimes|nullable|boolean',
                'requirePinForSensitiveActions' => 'sometimes|nullable|boolean',
                'storagePath' => 'sometimes|nullable|string|max:255',
                'maxCacheSize' => 'sometimes|nullable|integer',
                'autoUpdateEnabled' => 'sometimes|nullable|boolean',
                'supportContactEmail' => 'sometimes|nullable|email',
                'lastSyncTime' => 'sometimes|nullable|date_format:Y-m-d H:i:s',
                'fontSize' => 'sometimes|nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Obtener el usuario autenticado
            $user = auth()->user();

            // Obtener la configuración por defecto del sistema
            $defaultConfig = Configuration::where('isDefault', true)->first();

            // Intentar encontrar la configuración del usuario
            $userConfig = Configuration::where('user_id', $user->id)->first();

            // Si no existe, crear una nueva configuración para el usuario usando los valores por defecto
            if (!$userConfig) {
                $userConfig = new Configuration();
                $userConfig->user_id = $user->id;
                $userConfig->language = $user->language;
            }

            if (isset($data['language'])) {
                $userData = User::where('id', $user->id)->first();
                if (!$userData) {
                    return response()->json(['msg' => 'UserNotFound'], 404);
                }
                $userData->language = $data['language'];
                $userData->save(); // Guarda los cambios en el usuario
            }

            // Filtrar los datos para actualizar solo los campos presentes
            $configData = array_filter([
                'appName' => $data['appName'] ?? $userConfig->appName ?? $defaultConfig->appName,
                'appVersion' => $data['appVersion'] ?? $userConfig->appVersion ?? $defaultConfig->appVersion,
                'language' => $data['language'] ?? $userConfig->language ?? $defaultConfig->language,
                'defaultCurrency' => $data['defaultCurrency'] ?? $userConfig->defaultCurrency ?? $defaultConfig->defaultCurrency,
                'themeColor' => $data['themeColor'] ?? $userConfig->themeColor ?? $defaultConfig->themeColor,
                'backgroundColor' => $data['backgroundColor'] ?? $userConfig->backgroundColor ?? $defaultConfig->backgroundColor,
                'textColor' => $data['textColor'] ?? $userConfig->textColor ?? $defaultConfig->textColor,
                'buttonColor' => $data['buttonColor'] ?? $userConfig->buttonColor ?? $defaultConfig->buttonColor,
                'isDarkModeEnabled' => $data['isDarkModeEnabled'] ?? $userConfig->isDarkModeEnabled ?? $defaultConfig->isDarkModeEnabled,
                'notificationsEnabled' => $data['notificationsEnabled'] ?? $userConfig->notificationsEnabled ?? $defaultConfig->notificationsEnabled,
                'apiEndpoint' => $data['apiEndpoint'] ?? $userConfig->apiEndpoint ?? $defaultConfig->apiEndpoint,
                'connectionTimeout' => $data['connectionTimeout'] ?? $userConfig->connectionTimeout ?? $defaultConfig->connectionTimeout,
                'retryAttempts' => $data['retryAttempts'] ?? $userConfig->retryAttempts ?? $defaultConfig->retryAttempts,
                'useBiometricAuth' => $data['useBiometricAuth'] ?? $userConfig->useBiometricAuth ?? $defaultConfig->useBiometricAuth,
                'requirePinForSensitiveActions' => $data['requirePinForSensitiveActions'] ?? $userConfig->requirePinForSensitiveActions ?? $defaultConfig->requirePinForSensitiveActions,
                'storagePath' => $data['storagePath'] ?? $userConfig->storagePath ?? $defaultConfig->storagePath,
                'maxCacheSize' => $data['maxCacheSize'] ?? $userConfig->maxCacheSize ?? $defaultConfig->maxCacheSize,
                'autoUpdateEnabled' => $data['autoUpdateEnabled'] ?? $userConfig->autoUpdateEnabled ?? $defaultConfig->autoUpdateEnabled,
                'supportContactEmail' => $data['supportContactEmail'] ?? $userConfig->supportContactEmail ?? $defaultConfig->supportContactEmail,
                'lastSyncTime' => $data['lastSyncTime'] ?? $userConfig->lastSyncTime ?? $defaultConfig->lastSyncTime,
                'fontSize' => $data['fontSize'] ?? $userConfig->fontSize ?? $defaultConfig->fontSize,
            ], fn($value) => !is_null($value)); // Elimina los valores que sean null

            // Actualizar o crear la configuración
            $userConfig->updateOrCreate(['user_id' => $user->id], $configData);

            return response()->json(['msg' => 'ConfigurationkControllerOk', 'configuration' => $userConfig], 200);
        } catch (\Exception $e) {
            Log::info('ConfigurationkController->updateOrCreate');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
