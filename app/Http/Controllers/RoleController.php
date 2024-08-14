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
            return response()->json(['roles' => $roles], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los roles'], 500);
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
                return response()->json([
                    'msg' => $validator->errors()->all()
                ], 400);
            }
    
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
    
            return response()->json(['msg' => 'Rol creado exitosamente', 'role' => $role], 201);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al crear el rol'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca un rol");
        try {
            $request->validate([
                'id' => 'required|numeric'
            ]);
            $role = Role::find($request->id);
            if (!$role) {
                return response()->json([
                    'msg' => 'Rol no encontrado'
                ], 404);
            }
            return response()->json(['rol' => $role], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Rol no encontrado'], 404);
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
                'id' => 'required|numeric',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'msg' => $validator->errors()->all()
                ], 400);
            }
            
            $role = Role::find($request->id);
            if (!$role) {
                return response()->json([
                    'msg' => 'Rol no encontrado'
                ], 404);
            }
            $role->update([
                'name' => $request->name,
                'description' => $request->description
            ]);
    
            return response()->json(['msg' => 'Rol actualizado exitosamente', 'role' => $role], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al actualizar el rol'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Elimina un rol");
        try {
            $role = Role::find($request->id);
            if (!$role) {
                return response()->json([
                    'msg' => 'Rol no encontrado'
                ], 404);
            }
            $role->delete();
    
            return response()->json(['msg' => 'Rol eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Error al eliminar el rol'], 500);
        }
    }
}
