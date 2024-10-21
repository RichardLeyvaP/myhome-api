<?php

namespace App\Http\Controllers;

use App\Models\Home;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Accediendo a la lista de hogares");
        try {
            $homes = Home::get()->map(function ($home) {
                $getTranslatedHomeType = $home->homeType->getTranslatedHomeType();
                $getTranslatedHomeStatus = $home->getTranslatedHomeStatus();
                return [
                    'id' => $home->id,
                    'name' => $home->name,
                    'address' => $home->address,
                    'homeTypeId' => $home->home_type_id,
                    'nameHomeType' => $getTranslatedHomeType['name'],
                    'residents' => $home->residents,
                    'geoLocation' => $home->geo_location,
                    'timezone' => $home->timezone,
                    'status' => $getTranslatedHomeStatus['status'],
                    'image' => $home->image,
                ];
            });

            if ($homes->isEmpty()) {
                return response()->json(['homes' => $homes], 204);
            }

            return response()->json(['homes' => $homes], 200);
        } catch (\Exception $e) {
            Log::info('HomeController->index');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Creando un nuevo hogar");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'address' => 'required|string|max:255',
                'home_type_id' => 'required|exists:home_types,id', // Debe existir en la tabla home_types
                'residents' => 'nullable|integer',
                'geo_location' => 'nullable|string|max:255',
                'timezone' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:50',
                'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Procesar la imagen
            $filename = 'homes/default.jpg';

            // Crear el registro del hogar
            $home = Home::create([
                'name' => $request->name,
                'address' => $request->address,
                'home_type_id' => $request->home_type_id,
                'residents' => $request->residents,
                'geo_location' => $request->geolocation,
                'timezone' => $request->timezone,
                'status' => $request->status ?? 'Activa',
                'image' => $filename,
            ]);

            // Manejo de archivos adjuntos
            if ($request->hasFile('image')) {
                $filename = $request->file('image')->storeAs('homes', $home->id . '.' . $request->file('image')->extension(), 'public');
                $home->update(['image' => $filename]);
            }

            return response()->json(['msg' => 'HomeCreated', 'home' => $home], 201);
        } catch (\Exception $e) {
            Log::info('HomeController->store');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Accediendo a los detalles de un hogar");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:homes,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $home = Home::find($request->id);
            if (!$home) {
                return response()->json(['msg' => 'HomeNotFound'], 204);
            }
            $getTranslatedHomeType = $home->homeType->getTranslatedHomeType();
            $getTranslatedHomeStatus = $home->getTranslatedHomeStatus();

            return response()->json([
                'name' => $home->name,
                'address' => $home->address,
                'home_type_id' => $home->home_type_id,
                'nameHomeType' => $getTranslatedHomeType['name'],
                'residents' => $home->residents,
                'geoLocation' => $home->geo_location,
                'timezone' => $home->timezone,
                'status' => $getTranslatedHomeStatus['status'],
                'image' => $home->image,
            ], 200);
        } catch (\Exception $e) {
            Log::info('HomeController->show');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Editando un hogar");
        try {
            // Validación de los datos (usamos 'sometimes' para validar solo si están presentes)
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:homes,id',
                'name' => 'sometimes|nullable|string|max:255',
                'address' => 'sometimes|required|string|max:255',
                'home_type_id' => 'sometimes|required|exists:home_types,id',
                'residents' => 'sometimes|nullable|integer|min:1',
                'geo_location' => 'sometimes|nullable|string|max:255',
                'timezone' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|nullable|string|max:50',
                'image' => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Buscar el hogar
            $home = Home::find($request->id);
            if (!$home) {
                return response()->json(['msg' => 'HomeNotFound'], 404);
            }

            // Procesar la imagen si se sube una nueva
            $filename = $home->image;
            if ($request->hasFile('image')) {
                // Eliminar la imagen anterior si no es la predeterminada
                if ($home->image != 'homes/default.jpg' && Storage::disk('public')->exists($home->image)) {
                    Storage::disk('public')->delete($home->image);
                }
                // Guardar la nueva imagen
                $filename = $request->file('image')->storeAs('homes', $home->id . '.' . $request->file('image')->extension(), 'public');
            }

            // Filtrar los datos a actualizar
            $homeData = array_filter([
                'name' => $request->name ?? $home->name,
                'address' => $request->address ?? $home->address,
                'home_type_id' => $request->home_type_id ?? $home->home_type_id,
                'residents' => $request->residents ?? $home->residents,
                'geo_location' => $request->geolocation ?? $home->geolocation,
                'timezone' => $request->timezone ?? $home->timezone,
                'status' => $request->status ?? $home->status,
                'image' => $filename,
            ], fn($value) => !is_null($value));

            // Actualizar los datos del hogar
            $home->update($homeData);

            return response()->json(['msg' => 'HomeUpdated', 'home' => $home], 200);
        } catch (\Exception $e) {
            Log::info('HomeController->update');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Eliminando un hogar");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:homes,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Buscar el hogar
            $home = Home::find($request->id);
            if (!$home) {
                return response()->json(['msg' => 'HomeNotFound'], 404);
            }

            // Eliminar la imagen si no es la predeterminada
            if ($home->image != 'homes/default.jpg' && Storage::disk('public')->exists($home->image)) {
                Storage::disk('public')->delete($home->image);
            }

            // Eliminar el hogar
            $home->delete();

            return response()->json(['msg' => 'HomeDeleted'], 200);
        } catch (\Exception $e) {
            Log::info('HomeController->destroy');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
