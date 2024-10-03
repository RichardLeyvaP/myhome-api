<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\Status;
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
        //Log::info(auth()->user()->name . '-' . "Entra a buscar los productos");
        try {
            $products = Product::with('status', 'category')->get()->map(function ($query){
                $getTranslatedCategories = $query->category->getTranslatedCategories();
                $getTranslatedStatus = $query->status->getTranslatedStatus();
                return [
                    'id' => $query->id,
                    'name' => $query->name,
                    'categoryId' => $query->category_id,
                    'nameCategory' => $getTranslatedCategories['name'],
                    'statusId' => $query->status_id,
                    'nameStatus' => $getTranslatedStatus['name'],
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
            if ($products->isEmpty()) {
                return response()->json(['products' => $products], 204);
            }
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
        //Log::info(auth()->user()->name . '-' . "Crea un nuevo producto");
        try {
            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'status_id' => 'required|exists:statuses,id',
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
                $getTranslatedCategories = $query->category->getTranslatedCategories();
                $getTranslatedStatus = $query->status->getTranslatedStatus();
                return [
                    'id' => $query->id,
                    'name' => $query->name,
                    'categoryId' => $query->category_id,
                    'nameCategory' => $getTranslatedCategories['name'],
                    'statusId' => $query->status_id,
                    'nameStatus' => $getTranslatedStatus['name'],
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
                return response()->json(['msg' => 'ProductNotFound'], 204);
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
            // Validar solo los campos presentes en la petición
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:products,id',
                'name' => 'sometimes|required|string|max:255',
                'category_id' => 'sometimes|required|exists:categories,id',
                'status_id' => 'sometimes|required|exists:statuses,id',
                'quantity' => 'sometimes|required|integer|min:0',
                'unit_price' => 'sometimes|required|numeric|min:0',
                'purchase_date' => 'sometimes|required|date',
                'purchase_place' => 'sometimes|nullable|string|max:255',
                'expiration_date' => 'sometimes|nullable|date|after_or_equal:purchase_date',
                'brand' => 'sometimes|nullable|string|max:255',
                'additional_notes' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:2048', // max:2048 = 2MB
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
                if ($product->image_product != "products/default.jpg" && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Guardar el nuevo archivo
                $filename = $request->file('image')->storeAs('products', $product->id . '.' . $request->file('image')->extension(), 'public');
            }

            // Filtrar los campos que tienen datos en la petición
            $productData = array_filter([
                'name' => $request->name ?? null,
                'category_id' => $request->category_id ?? null,
                'status_id' => $request->status_id ?? null,
                'quantity' => $request->quantity ?? null,
                'unit_price' => $request->unit_price ?? null,
                'total_price' => isset($request->unit_price, $request->quantity) ? $request->unit_price * $request->quantity : null,
                'purchase_date' => $request->purchase_date ?? null,
                'purchase_place' => $request->purchase_place ?? null,
                'expiration_date' => $request->expiration_date ?? null,
                'brand' => $request->brand ?? null,
                'additional_notes' => $request->additional_notes ?? null,
                'image' => $filename,
            ], fn($value) => !is_null($value));

            // Actualizar el producto con los datos filtrados
            $product->update($productData);

            return response()->json(['msg' => 'ProductUpdateOk', 'product' => $product], 200);
        } catch (\Exception $e) {
            Log::error('ProductController->update');
            Log::error($e->getMessage());
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
            $personId = auth()->user()->person->id;
            if (!$personId) {
                return response()->json(['error' => 'Persona no encontrada'], 404);
            }
            $productcategories = Category::with('parent', 'children', 'people')->ofType('Product') // Cargar relaciones necesarias
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
                        'nameCategory' => $name,
                        'descriptionCategory' => $description,
                        'colorCategory' => $category->color,
                        'iconCategory' => $category->icon,
                        'parent_id' => $category->parent_id,
                        'children' => $this->mapChildrenCategory($category->children, $personId),
                    ];
                });
            /*$productcategories = Category::ofType('Product')->get()->map(function ($productcategory) {
                $translated = $productcategory->getTranslatedCategories();
                return [
                    'id' => $productcategory->id,
                    'nameCategory' => $translated['name'],
                    'descriptionCategory' => $translated['description'],
                    'colorCategory' => $productcategory->color,
                    'iconCategory' => $productcategory->icon,
                ];
            });*/

            $productstatus = Status::ofType('Product')->get()->map(function ($productstate) {
                $translated = $productstate->getTranslatedStatus();
                return [
                    'id' => $productstate->id,
                    'nameStatus' => $translated['name'],
                    'descriptionStatus' => $translated['description'],
                    'colorStatus' => $productstate->icon, // Aquí era 'colorStatus' pero el valor era 'icon'
                ];
            });
            return response()->json(['productcategories' => $productcategories, 'productstatus' => $productstatus], 200);
        } catch (\Exception $e) {
            Log::error('ProductController->productcategory_productstatus: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function mapChildrenCategory($children, $personId)
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
                'nameCategory' => $name,
                'descriptionCategory' => $description,
                'colorCategory' => $child->color,
                'iconCategory' => $child->icon,
                'parent_id' => $child->parent_id,
                'children' => $this->mapChildrenCategory($child->children, $personId), // Recursividad con personId
            ];
        });
    }
}
