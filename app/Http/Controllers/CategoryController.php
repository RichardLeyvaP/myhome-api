<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las categorias");
        try {
            //$categories = Category::all();
            $categories = Category::with('parent', 'children')->get()->map(function ($category) {
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
            
            return response()->json(['categories' => $categories]);
        } catch (\Exception $e) {
            Log::info('CategoryController->index');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
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
                'color' => 'required|string|size:7',
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
            Log::info($e);
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
                'id' => 'required|numeric'
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
            Log::info($e);
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
                'id' => 'required|numeric',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'sometimes|required|string|size:7',
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
                return response()->json(['msg' => 'CategoryNotfound'], 404);
            }
            $filename = $request->icon;
            if ($request->hasFile('icon'))
            if ($category->icon != 'categories/default_profile.jpg') {
                $destination = public_path("storage\\" . $category->icon);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $filename = $request->file('icon')->storeAs('categories', $category->id . '.' . $request->file('icon')->extension(), 'public');
            }
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'icon' => $filename,
                'parent_id' => $request->parent_id,
            ]);
    
            return response()->json(['msg' => 'CategoryUpdateOk', 'category' => $category], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->update');
            Log::info($e);
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
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $category = Category::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }
            if ($category->icon != 'categories/default_profile.jpg') {
                $destination = public_path("storage\\" . $category->icon);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
            }
            $category->delete();
    
            return response()->json(['msg' => 'CategoryDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('CategoryController->destroy');
            Log::info($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
