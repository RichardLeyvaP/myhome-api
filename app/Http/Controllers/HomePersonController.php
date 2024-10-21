<?php

namespace App\Http\Controllers;

use App\Models\Home;
use App\Models\HomePerson;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HomePersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Accediendo a la lista de hogares y personas");
        try {
            $homePersons = HomePerson::with(['home', 'person', 'role'])->get()->map(function ($homePerson) {
                $gettranslatedRoles = $homePerson->role->getTranslatedRoles();
                return [
                    'id' => $homePerson->id,
                    'homeId' => $homePerson->home_id,
                    'personIid' => $homePerson->person_id,
                    'roleIid' => $homePerson->role_id,
                    'home' => $homePerson->home->name, // Asumiendo que hay un método `name` en Home
                    'person' => $homePerson->person->name, // Asumiendo que hay un método `name` en Person
                    'role' => $gettranslatedRoles['name'], // Asumiendo que hay un método `name` en Role
                ];
            });

            if (!$homePersons) {
                return response()->json(['msg' => 'HomePersonNotFound'], 204);
            }

            return response()->json(['homePersons' => $homePersons], 200);
        } catch (\Exception $e) {
            Log::info('HomePersonController->index');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Creando relación entre hogar y persona");

        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'home_id' => 'required|exists:homes,id',
                'person_id' => 'required|exists:people,id',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Buscar el hogar y la persona
            $home = Home::find($request->home_id);
            $person = Person::find($request->person_id);

           // Verificar si la relación ya existe
            $existingRelation = $home->people()->where('person_id', $person->id)->first();

            if (!$existingRelation) {
                // Crear la relación entre hogar y persona
                $home->people()->attach($person->id, ['role_id' => $request->role_id]);

                // Obtener el nuevo registro creado
                $newRelation = $home->people()->where('person_id', $person->id)->first();
                return response()->json(['msg' => 'HomePersonStoreOK', 'homePerson' => $newRelation], 201);
            } else {
                // Devolver la relación existente
                return response()->json(['homePerson' => $existingRelation], 200);
            }
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
        Log::info(auth()->user()->name . '-' . "Accediendo a los detalles de una relación entre hogar y persona");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_person,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $homePerson = HomePerson::with(['home', 'person', 'role'])->find($request->id);
            if (!$homePerson) {
                return response()->json(['msg' => 'HomePersonNotFound'], 204);
            }
            $gettranslatedRoles = $homePerson->role->getTranslatedRoles();
            return response()->json([
                'id' => $homePerson->id,
                'homeId' => $homePerson->home_id,
                'personId' => $homePerson->person_id,
                'roleId' => $homePerson->role_id,
                'home' => $homePerson->home->name,
                'person' => $homePerson->person->name,
                'role' => $gettranslatedRoles['name'],
            ], 200);
        } catch (\Exception $e) {
            Log::info('HomePersonController->show');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Editando una relación entre hogar y persona");
        
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_person,id',
                'home_id' => 'sometimes|exists:homes,id',
                'person_id' => 'sometimes|exists:people,id',
                'role_id' => 'sometimes|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Buscar la relación
            $homePerson = HomePerson::find($request->id);
            if (!$homePerson) {
                return response()->json(['msg' => 'HomePersonNotFound'], 404);
            }

            // Verificar si hay cambios antes de actualizar
            $updated = false;

            if ($request->has('home_id') && $request->home_id != $homePerson->home_id) {
                $homePerson->home_id = $request->home_id;
                $updated = true;
            }

            if ($request->has('person_id') && $request->person_id != $homePerson->person_id) {
                $homePerson->person_id = $request->person_id;
                $updated = true;
            }

            if ($request->has('role_id') && $request->role_id != $homePerson->role_id) {
                $homePerson->role_id = $request->role_id;
                $updated = true;
            }

            // Si se realizaron cambios, actualiza el registro
            if ($updated) {
                $homePerson->save();
                return response()->json(['msg' => 'HomePersonUpdated', 'homePerson' => $homePerson], 200);
            }
        } catch (\Exception $e) {
            Log::info('HomePersonController->update');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Eliminando una relación entre hogar y persona");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:home_person,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Buscar la relación
            $homePerson = HomePerson::find($request->id);
            if (!$homePerson) {
                return response()->json(['msg' => 'HomePersonNotFound'], 404);
            }

            // Eliminar la relación
            $homePerson->delete();

            return response()->json(['msg' => 'HomePersonDeleted'], 200);
        } catch (\Exception $e) {
            Log::info('HomePersonController->destroy');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
