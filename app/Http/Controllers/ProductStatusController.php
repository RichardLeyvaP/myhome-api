<?php

namespace App\Http\Controllers;

use App\Models\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name . '-' . "Entra a buscar los estados de los productos");
        try {
            $productstatus = ProductStatus::all();
            $translatedProductStatus = [];

    foreach ($productstatus as $productstate) {
        $getTranslatedProducStatus = $productstate->getTranslatedProductStatus();
        $translatedProductStatus[] = [
            'id' => $productstate->id,
            'name' => $getTranslatedProducStatus['name'],
            'description' => $getTranslatedProducStatus['description'],
            'icon' => $productstate['icon']
        ];
    }
            return response()->json(['productstatus' => $translatedProductStatus], 200);
        } catch (\Exception $e) {
            Log::error('ProductStatusController->index: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Crea un nuevo estado de producto");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $status = ProductStatus::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json(['msg' => 'StatusCreatedSuccessfully', 'status' => $status], 201);
        } catch (\Exception $e) {
            Log::error('ProductStatusController->store: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entraa buscar un estado de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_statuses,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $productstatus = ProductStatus::where('id', $request->id)->get()->map(function ($productstatus){
                $getTranslatedProducStatus = $productstatus->getTranslatedProductStatus();
                return [
                    'id' => $productstatus->id,
                    'name' => $getTranslatedProducStatus['name'],
                    'description' => $getTranslatedProducStatus['description'],
                    'icon' => $productstatus['icon']
                ];
            });
            if (!$productstatus) {
                return response()->json(['msg' => 'StatusNotFound'], 404);
            }

            return response()->json(['status' => $productstatus], 200);
        } catch (\Exception $e) {
            Log::error('ProductStatusController->show: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Entra a modificra un estado de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_statuses,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $status = ProductStatus::find($request->id);
            if (!$status) {
                return response()->json(['msg' => 'StatusNotFound'], 404);
            }
            $status->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
    
            return response()->json(['msg' => 'StatusUpdatedSuccessfully', 'status' => $status], 200);
        } catch (\Exception $e) {
            Log::error('ProductStatusController->update: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Elimina un estado de producto");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:product_statuses,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $status = ProductStatus::find($request->id);
            if (!$status) {
                return response()->json(['msg' => 'StatusNotFound'], 404);
            }
            $status->delete();
    
            return response()->json(['msg' => 'StatusDeletedSuccessfully'], 200);
        } catch (\Exception $e) {
            Log::error('ProductStatusController->destroy: ' . $e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
