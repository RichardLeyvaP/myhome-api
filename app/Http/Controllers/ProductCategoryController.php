<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar las categorias de los productos");
        try {
            $productcategories = ProductCategory::all();
            $translatedProductCategories = [];

    foreach ($productcategories as $productcategory) {
        $getTranslatedProductCategories = $productcategory->getTranslatedProductCategories();
        $translatedProductCategories[] = [
            'id' => $productcategory->id,
            'name' => $getTranslatedProductCategories['name'],
            'description' => $getTranslatedProductCategories['description'],
            'icon' => $productcategory['icon']
        ];
    }
            return response()->json(['productcategories' => $translatedProductCategories], 200);
        } catch (\Exception $e) {
            Log::error('ProductCategoryController->index: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Crea una nueva categoria de los producto");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $category = ProductCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
            ]);

            return response()->json(['msg' => 'CategoryCreatedSuccessfully', 'category' => $category], 201);
        } catch (\Exception $e) {
            Log::error('ProductCategoryController->store: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar una categoria de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_categories,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $productcategories = ProductCategory::where('id', $request->id)->get()->map(function ($productcategory){
                $getTranslatedProductCategories = $productcategory->getTranslatedProductCategories();
                return [
                    'id' => $productcategory->id,
                    'name' => $getTranslatedProductCategories['name'],
                    'description' => $getTranslatedProductCategories['description'],
                    'icon' => $productcategory['icon']
                ];
            });
            if (!$productcategories) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }
            return response()->json(['productcategories' => $productcategories], 200);
        } catch (\Exception $e) {
            Log::error('ProductCategoryController->show: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a modificar una categoria de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_categories,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $category = ProductCategory::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
            ]);
    
            return response()->json(['msg' => 'CategoryUpdatedSuccessfully', 'category' => $category], 200);
        } catch (\Exception $e) {
            Log::error('ProductCategoryController->update: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a eliminar una categoria de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_categories,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $category = ProductCategory::find($request->id);
            if (!$category) {
                return response()->json(['msg' => 'CategoryNotFound'], 404);
            }
            $category->delete();
    
            return response()->json(['msg' => 'CategoryDeletedSuccessfully'], 200);
        } catch (\Exception $e) {
            Log::error('ProductCategoryController->destroy: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

}
