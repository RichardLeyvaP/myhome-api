<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info(auth()->user()->name.'-'."Entra a buscar los roles");
        try {
            $roles = Role::all();
            $translatedRoles = [];

            foreach ($roles as $rol) {
                $gettranslatedRoles = $rol->getTranslatedRoles();
                $translatedRoles[] = [
                    'id' => $rol->id,
                    'name' => $gettranslatedRoles['name'],
                    'description' => $gettranslatedRoles['description']
                ];
            }
            return response()->json(['roles' => $translatedRoles], 200);
        } catch (\Exception $e) {
            Log::info('RoleController->index');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Crea un nuevo rol");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
    
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
    
            return response()->json(['msg' => 'RoleStoreOk', 'role' => $role], 201);
        } catch (\Exception $e) {
            Log::info('RoleController->store');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca un rol");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:roles,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $roleTemp = Role::find($request->id);
            if (!$roleTemp) {
                return response()->json(['msg' => 'RoleNotfound'], 404);
            }

                $gettranslatedRoles = $roleTemp->getTranslatedRoles();
                $role [] = [
                    'id' => $roleTemp->id,
                    'name' => $gettranslatedRoles['name'],
                    'description' => $gettranslatedRoles['description']
                ];
            return response()->json(['rol' => $role], 200);
        } catch (\Exception $e) {
            Log::info('RoleController->show');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Edita un rol");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:roles,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            
            $role = Role::find($request->id);
            if (!$role) {
                return response()->json(['msg' => 'RoleNotFound'], 404);
            }
            $role->update([
                'name' => $request->name,
                'description' => $request->description
            ]);
    
            return response()->json(['msg' => __('RoleUpdateOk'), 'role' => $role], 200);
        } catch (\Exception $e) {
            Log::info('RoleController->update');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina un rol");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:roles,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            $role = Role::find($request->id);
            if (!$role) {
                return response()->json(['msg' => 'RoleNotFound'], 404);
            }
            $role->delete();
    
            return response()->json(['msg' => 'RoleDeleteOk'], 200);
        } catch (\Exception $e) {
            Log::info('RoleController->destroy');
            Log::info($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
