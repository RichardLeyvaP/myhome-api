<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryPerson;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las categorías asociadas");
        try {
            // Obtener el ID de la persona relacionada con el usuario autenticado
            $personId = auth()->user()->person->id;
            // Obtener todas las categorías que estén relacionadas con la persona
            $categories = Category::with('parent', 'children', 'people') // Cargar relaciones necesarias
                ->get()
                ->filter(function ($category) use ($personId) {
                    // Filtrar las categorías que están relacionadas con la persona o tienen state = 1
                    return $category->people->contains('id', $personId) || $category->state == 1;
                })
                ->map(function ($category) use ($personId){
                    if ($category->state == 1) {
                        $translatedAttributes = $category->getTranslatedCategories();
                        $name = $translatedAttributes['name'];
                        $description = $translatedAttributes['description'];
                    } else {
                        $name = $category->name;
                        $description = $category->description;
                    }
                    return [
                        'id' => $category->id,
                        'name' => $name,
                        'description' => $description,
                        'color' => $category->color,
                        'icon' => $category->icon,
                        'parent_id' => $category->parent_id,
                        'children' => $this->mapChildren($category->children, $personId),
                    ];
                })->values();
    
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            Log::info('CategoryPersonController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function mapChildren($children, $personId)
    {
        // Filtrar solo los hijos que estén relacionados con la persona o tengan state = 1
        return $children->filter(function ($child) use ($personId) {
            // Verificamos si el hijo está relacionado con la persona o tiene state = 1
            return $child->people->contains('id', $personId) || $child->state == 1;
        })
        ->map(function ($child) use ($personId) {
            if ($child->state == 1) {
                // Obtener los atributos traducidos si el estado es 1
                $translatedAttributes = $child->getTranslatedCategories();
                $name = $translatedAttributes['name'];
                $description = $translatedAttributes['description'];
            } else {
                // Usar los atributos originales si el estado no es 1
                $name = $child->name;
                $description = $child->description;
            }
            return [
                'id' => $child->id,
                'name' => $name,
                'description' => $description,
                'color' => $child->color,
                'icon' => $child->icon,
                'parent_id' => $child->parent_id,
                'children' => $this->mapChildren($child->children, $personId), // Recursividad con personId
            ];
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea una nueva categoría asociada a la persona");
        // Obtener el ID de la persona relacionada con el usuario autenticado
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'required|string',
                'type' => 'required|string',
                'icon' => [
                    'nullable',
                    Rule::when($request->hasFile('icon'), ['file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], 'string')
                ],
                'parent_id' => 'nullable|exists:categories,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $personId = auth()->user()->person->id;

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'type' => $request->type,
                'state' => 0,
                'parent_id' => $personId,
            ]);

            if ($request->hasFile('icon')) {
                $filename = $request->file('icon')->storeAs('categories', $category->id . '.' . $request->file('icon')->extension(), 'public');
                $category->icon = $filename;
                $category->save();
            } else {
                $category->icon = $request->icon;
                $category->save();
            }

            // Relacionar la categoría con la persona en category_person
        $category->people()->attach($request->person_id); // Asumiendo que tienes esta relación definida en el modelo Category
        
            return response()->json(['msg' => 'CategoryPersonStoreOk', 'category' => $category], 201);
        } catch (\Exception $e) {
            Log::info('CategoryPersonController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar una categoría específica Asociada a el");

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:categories,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            // Obtener el ID de la persona relacionada con el usuario autenticado
            $personId = auth()->user()->person->id;

            // Buscar la categoría específica por ID, incluyendo relaciones
            $category = Category::where('id', $request->id)->with('parent', 'children', 'people') // Cargar relaciones necesarias
                ->get()
                ->filter(function ($category) use ($personId) {
                    // Filtrar las categorías que están relacionadas con la persona o tienen state = 1
                    return $category->people->contains('id', $personId) || $category->state == 1;
                })
                ->map(function ($category) use ($personId){
                    if ($category->state == 1) {
                        $translatedAttributes = $category->getTranslatedCategories();
                        $name = $translatedAttributes['name'];
                        $description = $translatedAttributes['description'];
                    } else {
                        $name = $category->name;
                        $description = $category->description;
                    }
                    return [
                        'id' => $category->id,
                        'name' => $name,
                        'description' => $description,
                        'color' => $category->color,
                        'icon' => $category->icon,
                        'parent_id' => $category->parent_id,
                        'children' => $this->mapChildren($category->children, $personId),
                    ];
                });

            return response()->json(['category' => $category], 200);
        } catch (\Exception $e) {
            Log::info('CategoryPersonController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita una categoría asociada a la persona");

        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:categories,id', // ID de la categoría
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'sometimes|required|string',
                'icon' => [
                    'nullable',
                    Rule::when($request->hasFile('icon'), ['file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], 'string')
                ],
                'parent_id' => 'nullable|exists:categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Encontrar la categoría a actualizar
            $category = Category::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }

            // Manejo del icono
            $filename = $category->icon; // Asignar valor por defecto
            if ($request->hasFile('icon')) {
                if ($category->icon != 'categories/default.jpg') {
                    if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                        Storage::disk('public')->delete($category->icon);
                    }
                }
                $filename = $request->file('icon')->storeAs('categories', $category->id . '.' . $request->file('icon')->extension(), 'public');
            }

            // Actualizar los datos de la categoría
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'icon' => $filename,
                'parent_id' => $request->parent_id,
            ]);

            return response()->json(['msg' => 'CategoryPersonUpdateOk', 'category' => $category], 200);
        } catch (\Exception $e) {
            Log::info('CategoryPersonController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(request $request)
    {
        Log::info(auth()->user()->name . '-' . "Eliminando una categoria asociada a la persona");
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|numeric|exists:categories,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $personId = auth()->user()->person->id;
            
            $person = Person::find($personId);

            // Comprobar si la relación existe
            $relationExists = $person->categories()->where('category_id', $request->category_id)->exists();            
            if (!$relationExists) {
                return response()->json(['msg' => 'NoFound'], 204);
            }
            // Eliminar la relación
            $person->categories()->detach($request->category_id);

            return response()->json(['msg' => 'CategoryPersonDeleted'], 200);

        } catch (\Exception $e) {
            Log::error('CategoryPersonController->destroy: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
