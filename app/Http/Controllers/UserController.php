<?php

namespace App\Http\Controllers;

use App\Helpers\AppConstants;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

/*#[Group('Administrador', 'Endpoints de Administración')]
#[Subgroup('User', 'Endpoints de Usuarios')]*/
class UserController extends Controller
{
    public function __construct() {
    }


    public function index()
    {
        try {

            Log::info("Entra a buscar los usuarios");
            return response()->json(['users' => User::all()], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['msg' => "Error interno del sistema"], 500);
        }
    }

    public function register(Request $request)
    {
        Log::info("Registrar usuarios");
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required|confirmed',
                'email' => 'required|max:50|email|unique:users'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'msg' => $validator->errors()->all()
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'msg' => "Client registrado correctamente!!!",
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['msg' => $th->getMessage() . 'Error interno del sistema'], 500);
        }
    }
}
