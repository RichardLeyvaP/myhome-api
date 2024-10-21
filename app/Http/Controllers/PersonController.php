<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Accediendo a la lista de personas");
        try {
            $people = Person::get()->map(function ($person) {
                $translatedGender = $person->getTranslatedGender();
                return [
                    'user_id' => $person->user_id,
                    'name' => $person->name,
                    'birthDate' => $person->birth_date,
                    'age' => $person->age,
                    'gender' => $translatedGender['gender'],
                    'email' => $person->email,
                    'phone' => $person->phone,
                    'address' => $person->address,
                    'image' => $person->image,
                ];
            });

            return response()->json(['people' => $people], 200);
        } catch (\Exception $e) {
            Log::info('PeopleController->index');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Creando una nueva persona");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id', // Debe existir en la tabla users
                'name' => 'required|string|max:255',
                'birth_date' => 'nullable|required|date',
                'age' => 'nullable|integer|min:0',
                'gender' => 'nullable|string|max:10',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:255',
                'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Procesar la imagen
            $filename = 'people/default.jpg';
            // Crear el registro de la persona
            $person = Person::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'gender' => $request->gender,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $filename,
            ]);

            // Manejo de archivos adjuntos
            if ($request->hasFile('image')) {
                $filename = $request->file('image')->storeAs('people', $person->id . '.' . $request->file('image')->extension(), 'public');
                // Actualizar el registro con la ruta del archivo
                $person->update(['image' => $filename]);
            }

            return response()->json(['msg' => 'PersonCreated', 'person' => $person], 201);
        } catch (\Exception $e) {
            Log::info('PeopleController->store');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Accediendo a los detalles de una persona");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:people,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $person = Person::find($request->id);
            if (!$person) {
                return response()->json(['msg' => 'PersonNotFound'], 404);
            }
            $translatedGender = $person->getTranslatedGender();
            return response()->json([
                'name' => $person->name,
                'birthDate' => $person->birth_date,
                'age' => $person->age,
                'gender' => $translatedGender['gender'],
                'email' => $person->email,
                'phone' => $person->phone,
                'address' => $person->address,
                'image' => $person->image,
            ], 200);
        } catch (\Exception $e) {
            Log::info('PeopleController->show');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Editando una persona");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:people,id',
                'name' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'age' => 'nullable|integer|min:0', 
                'gender' => 'nullable|string|max:10',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|max:15',
                'address' => 'nullable|string|max:255',
                'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Buscar la persona
            $person = Person::find($request->id);
            if (!$person) {
                return response()->json(['msg' => 'PersonNotFound'], 404);
            }

            // Procesar la imagen si se sube una nueva
            $filename = $person->image;
            if ($request->hasFile('image')) {
                // Verificar si el archivo existe y eliminarlo
                if ($person->image != 'people/default.jpg' && Storage::disk('public')->exists($person->image)) {
                    Storage::disk('public')->delete($person->image);
                    $filename = $request->file('image')->storeAs('people', $person->id . '.' . $request->file('image')->extension(), 'public');
                }
            }

            // Actualizar los datos de la persona
            $person->update([
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'gender' => $request->gender,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $filename,
            ]);

            return response()->json(['msg' => 'PersonUpdated', 'person' => $person], 200);
        } catch (\Exception $e) {
            Log::info('PeopleController->update');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Eliminando una persona");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:people,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Buscar la persona
            $person = Person::find($request->id);
            if (!$person) {
                return response()->json(['msg' => 'PersonNotFound'], 404);
            }

            // Eliminar la imagen si no es la predeterminada
            if ($person->image != 'people/default.jpg' && Storage::disk('public')->exists($person->image)) {
                Storage::disk('public')->delete($person->image);
            }

            // Eliminar la persona
            $person->delete();

            return response()->json(['msg' => 'PersonDeleted'], 200);
        } catch (\Exception $e) {
            Log::info('PeopleController->destroy');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
