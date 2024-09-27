<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\VarExporter\Internal\Values;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las categorias");
        try {
            $categories = Category::with('parent', 'children')
            ->get()
            ->filter(function ($category) {
                // Solo mostrar categorías que no tienen padre (categorías principales)
                return $category->parent_id === null;
            })
            ->map(function ($category) {
                $translatedAttributes = $category->getTranslatedCategories();
                return [
                    'id' => $category->id,
                    'name' => $translatedAttributes['name'],
                    'description' => $translatedAttributes['description'],
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'parent_id' => $category->parent_id,
                    //'parent' => $category->parent ? $this->mapParent($category->parent) : null,
                    'children' => $this->mapChildren($category->children),
                ];
            })->Values();
            if (!$categories) {
                return response()->json(['msg' => 'CategoryNotfound'], 204);
            }
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

        /**
     * Función para mapear el padre de una categoría (si existe).
     */
    public function mapParent($parent)
    {
        return [
            'id' => $parent->id,
            'name' => $parent->name,
            'description' => $parent->description,
            'color' => $parent->color,
            'icon' => $parent->icon,
            'parent_id' => $parent->parent_id,
            'parent' => $parent->parent ? $this->mapParent($parent->parent) : null, // Mapeo recursivo para ancestros
        ];
    }

    /**
     * Función recursiva para mapear categorías hijas.
     */
    public function mapChildren($children)
    {
        return $children->map(function ($child) {
            $translatedAttributes = $child->getTranslatedCategories();
            return [
                'id' => $child->id,
                'name' => $translatedAttributes['name'],
                'description' => $translatedAttributes['description'],
                'color' => $child->color,
                'icon' => $child->icon,
                'parent_id' => $child->parent_id,
                'children' => $this->mapChildren($child->children), // Recursión para los hijos de los hijos
            ];
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea una nueva categoria");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'required|string',
                'type' => 'required|string',
                'icon' => [
                'nullable',
                Rule::when($request->hasFile('icon'), ['file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], 'string')],
                'parent_id' => 'nullable|exists:categories,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'type' => $request->type,
                'state' => 1,
                'parent_id' => $request->parent_id,
            ]);

            //$filename = "categories/default_profile.jpg";
            if ($request->hasFile('icon')) {
                $filename = $request->file('icon')->storeAs('categories', $category->id . '.' . $request->file('icon')->extension(), 'public');
                $category->icon = $filename;
                $category->save();
            } 
            else {
                $category->icon = $request->icon;
                $category->save();
            }
            
    
            return response()->json(['msg' => 'CategoryStoreOk', 'category' => $category], 201);
        } catch (\Exception $e) {
            Log::info('CategoryController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca una categoria");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:categories,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $category = Category::where('id', $request->id)->with('parent', 'children')->get()->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'parent_id' => $category->parent_id,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                        'description' => $category->description,
                        'color' => $category->parent->color,
                        'icon' => $category->parent->icon,
                        'parent_id' => $category->parent->parent_id,
                    ] : null,
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'description' => $child->description,
                            'color' => $child->color,
                            'icon' => $child->icon,
                            'parent_id' => $child->parent_id,
                        ];
                    }),
                ];
            });
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotfound'], 404);
            }
            return response()->json(['category' => $category], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita una categoria");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:categories,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'sometimes|required|string',
                'type' => 'sometimes|required|string',
                'state' => 'sometimes|required|integer',
                'icon' => [
                'nullable',
                Rule::when($request->hasFile('icon'), ['file', 'mimes:jpeg,png,jpg,gif', 'max:2048'], 'string')],
                'parent_id' => 'nullable|exists:categories,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $category = Category::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotfound'], 204);
            }
            $filename = $request->icon;
            if ($request->hasFile('icon'))
            if ($category->icon != 'categories/default_profile.jpg') {
                if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                    Storage::disk('public')->delete($category->icon);
                }
                /*$destination = public_path("storage\\" . $category->icon);
                if (File::exists($destination)) {
                    File::delete($destination);
                }*/
                $filename = $request->file('icon')->storeAs('categories', $category->id . '.' . $request->file('icon')->extension(), 'public');
            }
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'icon' => $filename,
                'type' => $request->type,
                'state' => $request->state,
                'parent_id' => $request->parent_id,
            ]);
    
            return response()->json(['msg' => 'CategoryUpdateOk', 'category' => $category], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina una categoria");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:categories,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $category = Category::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }
            if ($category->icon != 'categories/default_profile.jpg') {
                /*$destination = public_path("storage\\" . $category->icon);
                if (File::exists($destination)) {
                    File::delete($destination);
                }*/
                if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                    Storage::disk('public')->delete($category->icon);
                }
            }
            $category->delete();
    
            return response()->json(['msg' => 'CategoryDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->destroy');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
