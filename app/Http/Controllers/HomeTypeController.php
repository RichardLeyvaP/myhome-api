<?php

namespace App\Http\Controllers;

use App\Models\HomeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HomeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar los tipos de viviendas");
        try {
            $hometypes = HomeType::all();
            $translatedHomeTypes = [];

            foreach ($hometypes as $hometype) {
                $getTranslatedHomeType = $hometype->getTranslatedHomeType();
                $translatedHomeTypes[] = [
                    'id' => $hometype->id,
                    'name' => $getTranslatedHomeType['name'],
                    'description' => $getTranslatedHomeType['description'] ?? null,
                    'icon' => $hometype['icon'] ?? null // si tienes un icono
                ];
            }

            return response()->json(['hometypes' => $translatedHomeTypes], 200);
        } catch (\Exception $e) {
            Log::error('HomeTypeController->index: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Crea un nuevo tipo de vivienda");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string', // si quieres manejar íconos
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $hometype = HomeType::create([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
            ]);

            return response()->json(['msg' => 'HomeTypeCreatedSuccessfully', 'hometype' => $hometype], 201);
        } catch (\Exception $e) {
            Log::error('HomeTypeController->store: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar un tipo de vivienda");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_types,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $hometype = HomeType::where('id', $request->id)->get()->map(function ($hometype) {
                $getTranslatedHomeType = $hometype->getTranslatedHomeType();
                return [
                    'id' => $hometype->id,
                    'name' => $getTranslatedHomeType['name'],
                    'description' => $getTranslatedHomeType['description'] ?? null,
                    'icon' => $hometype['icon'] ?? null // si tienes un icono
                ];
            });

            if (!$hometype) {
                return response()->json(['msg' => 'HomeTypeNotFound'], 404);
            }

            return response()->json(['hometype' => $hometype], 200);
        } catch (\Exception $e) {
            Log::error('HomeTypeController->show: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a modificar un tipo de vivienda");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_types,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string', // si tienes un icono
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $hometype = HomeType::find($request->id);
            if (!$hometype) {
                return response()->json(['msg' => 'HomeTypeNotFound'], 404);
            }

            $hometype->update([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
            ]);

            return response()->json(['msg' => 'HomeTypeUpdatedSuccessfully', 'hometype' => $hometype], 200);
        } catch (\Exception $e) {
            Log::error('HomeTypeController->update: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Elimina un tipo de vivienda");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_types,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $hometype = HomeType::find($request->id);
            if (!$hometype) {
                return response()->json(['msg' => 'HomeTypeNotFound'], 404);
            }

            $hometype->delete();

            return response()->json(['msg' => 'HomeTypeDeletedSuccessfully'], 200);
        } catch (\Exception $e) {
            Log::error('HomeTypeController->destroy: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
