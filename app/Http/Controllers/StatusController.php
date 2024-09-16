<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las estados");
        try {
            $status = Status::all();
            $translatedStatuses = [];

    foreach ($status as $state) {
        $getTranslatedStatus = $state->getTranslatedStatus();
        $translatedStatuses[] = [
            'id' => $state->id,
            'name' => $getTranslatedStatus['name'],
            'description' => $getTranslatedStatus['description'],
            'color' => $state->color,
            'created_at' => $state->created_at,
            'updated_at' => $state->updated_at,
        ];
    }
            return response()->json(['status' => $translatedStatuses], 200);
        } catch (\Exception $e) {
            Log::info('StatusController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea un nuevo estado");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'required|string|size:7'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $Status = Status::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color
            ]);
    
            return response()->json(['msg' => 'StatusStoreOk', 'Status' => $Status], 201);
        } catch (\Exception $e) {
            Log::info('StatusController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca un estado");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $Status = Status::find($request->id);
            if (!$Status) {
                return response()->json(['msg' => 'StatusNotfound'], 404);
            }
            return response()->json(['Status' => $Status], 200);
        } catch (\Exception $e) {
            Log::info('StatusController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita un estado");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'sometimes|required|string|size:7'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $Status = Status::find($request->id);
            if (!$Status) {
                return response()->json(['msg' => 'StatusNotfound'], 404);
            }
            $Status->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color
            ]);
    
            return response()->json(['msg' => 'StatusUpdateOk', 'Status' => $Status], 200);
        } catch (\Exception $e) {
            Log::info('StatusController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina un estado");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $Status = Status::find($request->id);
            if (!$Status) {
                return response()->json(['msg' => 'StatusNotFound'], 404);
            }
            $Status->delete();
    
            return response()->json(['msg' => 'StatusDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('StatusController->destroy');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
