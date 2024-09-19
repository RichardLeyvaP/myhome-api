<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar los productos");
        try {
            $products = Product::with('status', 'category')->get()->map(function ($query){
                $getTranslatedProductCategories = $query->category->getTranslatedProductCategories();
                $getTranslatedProducStatus = $query->status->getTranslatedProductStatus();
                return [
                    'id' => $query->id,
                    'name' => $query->name,
                    'categoryId' => $query->category_id,
                    'nameCategory' => $getTranslatedProductCategories['name'],
                    'statusId' => $query->status_id,
                    'nameStatus' => $getTranslatedProducStatus['name'],
                    'quantity' => $query->quantity,
                    'unitPrice' => $query->unit_price,
                    'totalPrice' => $query->total_price,
                    'purchaseDate' => $query->purchase_date,
                    'expirationDate' => $query->expiration_date,
                    'purchasePlace' => $query->purchase_place,
                    'brand' => $query->brand,
                    'additionalNotes' => $query->additional_notes,
                    'image' => $query->image,
                ];
            });
            return response()->json(['products' => $products], 200);
        } catch (\Exception $e) {
            Log::error('ProductController->index: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Crea un nuevo producto");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:product_categories,id',
                'status_id' => 'required|exists:product_statuses,id',
                'quantity' => 'required|integer|min:0',
                'unit_price' => 'required|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'purchase_place' => 'nullable|string|max:255',
                'expiration_date' => 'nullable|date|after_or_equal:purchase_date',
                'brand' => 'nullable|string',
                'additional_notes' => 'nullable|string',
                'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
                        
            $filename = 'products/default.jpg';
            // Crear el registro en la base de datos con los datos proporcionados
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'status_id' => $request->status_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->unit_price * $request->quantity,
                'purchase_date' => $request->purchase_date,
                'purchase_place' => $request->purchase_place,
                'expiration_date' => $request->expiration_date,
                'brand' => $request->brand,
                'additional_notes' => $request->additional_notes,
                'image' => $filename
            ]);
            // Manejo de archivos adjuntos
            if ($request->hasFile('image')) {
                $filename = $request->file('image')->storeAs('products', $product->id . '.' . $request->file('image')->extension(), 'public');
                // Actualizar el registro con la ruta del archivo
                $product->update(['image' => $filename]);
            }

            return response()->json(['msg' => 'ProductStoreOk', 'product' => $product], 201);
        } catch (\Exception $e) {
            Log::info('ProductController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar un producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:products,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $product = Product::with('status', 'category')->where('id', $request->id)->get()->map(function ($query){
                $getTranslatedProductCategories = $query->category->getTranslatedProductCategories();
                $getTranslatedProducStatus = $query->status->getTranslatedProductStatus();
                return [
                    'id' => $query->id,
                    'name' => $query->name,
                    'categoryId' => $query->category_id,
                    'nameCategory' => $getTranslatedProductCategories['name'],
                    'statusId' => $query->status_id,
                    'nameStatus' => $getTranslatedProducStatus['name'],
                    'quantity' => $query->quantity,
                    'unitPrice' => $query->unit_price,
                    'totalPrice' => $query->total_price,
                    'purchaseDate' => $query->purchase_date,
                    'expirationDate' => $query->expiration_date,
                    'purchasePlace' => $query->purchase_place,
                    'brand' => $query->brand,
                    'additionalNotes' => $query->additional_notes,
                    'image' => $query->image,
                ];
            });
            if (!$product) {
                return response()->json(['msg' => 'ProductNotFound'], 404);
            }
            return response()->json(['product' => $product], 200);
        } catch (\Exception $e) {
            Log::error('ProductController->show: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Edita un producto");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:products,id',
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:product_categories,id',
                'status_id' => 'required|exists:product_statuses,id',
                'quantity' => 'required|integer|min:0',
                'unit_price' => 'required|numeric|min:0',
                'purchase_date' => 'required|date',
                'purchase_place' => 'nullable|string|max:255',
                'expiration_date' => 'nullable|date|after_or_equal:purchase_date',
                'brand' => 'nullable|string|max:255',
                'additional_notes' => 'nullable|string',
                'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
            ]);

            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            // Buscar el producto
            $product = Product::find($request->id);
            if (!$product) {
                return response()->json(['msg' => 'ProductNotFound'], 404);
            }

            // Manejo de archivos adjuntos
            $filename = $product->image;
            if ($request->hasFile('image')) {
                // Verificar si el archivo existe y eliminarlo
                if ($product->image_product != "products/default.jpg")
                {
                    if ($product->image && Storage::disk('public')->exists($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                // Guardar el nuevo archivo
                $filename = $request->file('image')->storeAs('products', $product->id . '.' . $request->file('image')->extension(), 'public');
                }
            }

            // Actualizar el producto con los datos proporcionados
            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'status_id' => $request->status_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->unit_price * $request->quantity,
                'purchase_date' => $request->purchase_date,
                'purchase_place' => $request->purchase_place,
                'expiration_date' => $request->expiration_date,
                'brand' => $request->brand,
                'additional_notes' => $request->additional_notes,
                'image' => $filename,
            ]);

            return response()->json(['msg' => 'ProductUpdateOk', 'product' => $product], 200);
        } catch (\Exception $e) {
            Log::info('ProductController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Elimina un producto");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:products,id'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
    
            // Buscar el producto
            $product = Product::find($request->id);
            if (!$product) {
                return response()->json(['msg' => 'ProductNotFound'], 404);
            }
    
            if ($product->image != 'products/default.jpg' && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            // Eliminar el producto
            $product->delete();
    
            return response()->json(['msg' => 'ProductDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('ProductController->destroy');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function productcategory_productstatus()
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar las categorias y estado de los productos");
        try {
            $productcategories = ProductCategory::all();
            $translatedProductCategories = [];

            foreach ($productcategories as $productcategory) {
                $getTranslatedProductCategories = $productcategory->getTranslatedProductCategories();
                $translatedProductCategories[] = [
                    'id' => $productcategory->id,
                    'name' => $getTranslatedProductCategories['name'],
                    'description' => $getTranslatedProductCategories['description']
                ];
            }

            $productstatus = ProductStatus::all();
            $translatedProductStatus = [];

            foreach ($productstatus as $productstate) {
                $getTranslatedProducStatus = $productstate->getTranslatedProductStatus();
                $translatedProductStatus[] = [
                    'id' => $productstate->id,
                    'name' => $getTranslatedProducStatus['name'],
                    'description' => $getTranslatedProducStatus['description']
                ];
            }
            return response()->json(['productcategories' => $translatedProductCategories, 'productstatus' => $translatedProductStatus], 200);
        } catch (\Exception $e) {
            Log::error('ProductController->productcategory_productstatus: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
