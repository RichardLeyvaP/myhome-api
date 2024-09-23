<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar las prioridades");
        try {
            $priorities = Priority::all();
            $translatedPriorities = [];

        foreach ($priorities as $priority) {
            $translatedAttributes = $priority->getTranslatedAttributes();
            $translatedPriorities[] = [
                'id' => $priority->id,
                'name' => $translatedAttributes['name'],
                'description' => $translatedAttributes['description'],
                'color' => $priority->color,
                'level' => $priority->level
            ];
        }
            return response()->json(['priorities' => $translatedPriorities], 200);
        } catch (\Exception $e) {
            Log::info('PriorityController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea una nueva prioridad");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'required|string|size:7',
                'level' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }

            $priorities = Priority::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'level' => $request->level
            ]);
    
            return response()->json(['msg' => 'PrioritiesStoreOk', 'Priorities' => $priorities], 201);
        } catch (\Exception $e) {
            Log::info('PriorityController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca una prioridad");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:priorities,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $priority = Priority::find($request->id);
            if (!$priority) {
                return response()->json(['msg' => 'PriorityNotfound'], 404);
            }
            $getTranslatedPriorities = $priority->getTranslatedAttributes();
            $priorities [] = [
                'id' => $priority->id,
                'name' => $getTranslatedPriorities['name'],
                'description' => $getTranslatedPriorities['description'],
                'color' => $priority->color,
                'level' => $priority->level
            ];
            return response()->json(['Priorities' => $priorities], 200);
        } catch (\Exception $e) {
            Log::info('PriorityController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita una prioridad");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'required|string|size:7',
                'level' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $priorities = Priority::find($request->id);
            if (!$priorities) {
                return response()->json(['msg' => 'PriorityNotfound'], 404);
            }
            $priorities->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'level' => $request->level,
            ]);
    
            return response()->json(['msg' => 'PriorityUpdateOk', 'Priorities' => $priorities], 200);
        } catch (\Exception $e) {
            Log::info('PriorityController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina una prioridad");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $priorities = Priority::find($request->id);
            if (!$priorities) {
                return response()->json(['msg' => 'PriorityNotFound'], 404);
            }
            $priorities->delete();
    
            return response()->json(['msg' => 'PriorityDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('PriorityController->destroy');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
