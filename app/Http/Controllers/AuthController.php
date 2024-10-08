<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info("Entra a loguearse al Sistema");
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
            Log::info("obtener el usuario");
            $user = [];
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
            }
            // Intentar la autenticación con el nombre de usuario
            elseif (Auth::attempt(['name' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
            }
            Log::info($user);
            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;
                //return $branch;
                return response()->json([
                    'id' => $user->id,
                    'userName' => $user->name,
                    'email' => $user->email,
                    'token' => $token,
                ], 200, [], JSON_NUMERIC_CHECK);
            } else {
                return response()->json(["msg" => "Usuario no registrado"], 401);
            }
        } catch (\Throwable $th) {
            Log::info('AuthController->login');
            Log::error($th);
            return response()->json(['msg' =>'ServerError'], 500);
        }
    }

    public function logout(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Cierra Session");
        try {
            auth()->user()->tokens()->delete();
            return response()->json(["msg" => "CloseSessionOk"], 200);
        } catch (\Throwable $th) {
            Log::info('AuthController->logout');
            Log::error($th);
            return response()->json(['msg' => 'ServerError'], 500);
        }
    }

    public function googleCallback()
    {
        Log::info('Logueo por cuenta de google');
        try {
            // Intentar obtener el usuario de Google
            $userGoogle = Socialite::driver('google')->stateless()->user();

            // Verificar si $userGoogle es null
            if (!$userGoogle) {
                return response()->json(['msg' => 'GoogleNotFound',], 400);
            }

            $userExits = User::where('external_id', $userGoogle->id)
                ->where('external_auth', 'google')
                ->first();

            if ($userExits) {
                Auth::login($userExits);
            } else {
                // Buscar el usuario existente por correo electrónico
                $emailExits = User::where('email', $userGoogle->email)->first();
                if ($emailExits) {
                    $emailExits->update([
                        'external_id' => $userGoogle->id,
                        'external_auth' => 'google',
                    ]);
                    $userExits = $emailExits;
                    Auth::login($userExits);
                } else {
                    $userExits = User::create([
                        'name' => $userGoogle->name,
                        'email' => $userGoogle->email,
                        'external_id' => $userGoogle->id,
                        'external_auth' => 'google',
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;

            // Retornar los datos del usuario junto con el token
            return response()->json([
                'id' => $userExits->id,
                'userName' => $userExits->name,
                'email' => $userExits->email,
                'token' => $token,
            ], 200, [], JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            // Captura cualquier excepción que pueda ocurrir durante el proceso
            Log::info('AuthController->googleCallback');
            Log::error($e);
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function facebookCallback()
    {
        Log::info('Logueo por cuenta de facebook');
        try {
            // Intentar obtener el usuario de Google
            $userFacebook = Socialite::driver('facebook')->stateless()->user();

            // Verificar si $userFacebook es null
            if (!$userFacebook) {
                return response()->json(['msg' => 'FacebookNotFound'], 400);
            }

            $userExits = User::where('external_id', $userFacebook->id)
                ->where('external_auth', 'facebook')
                ->first();

            if ($userExits) {
                Auth::login($userExits);
            } else {
                // Buscar el usuario existente por correo electrónico
                $emailExits = User::where('email', $userFacebook->email)->first();
                if ($emailExits) {
                    $emailExits->update([
                        'external_id' => $userFacebook->id,
                        'external_auth' => 'facebook',
                    ]);
                    $userExits = $emailExits;
                    Auth::login($userExits);
                } else {
                    $userExits = User::create([
                        'name' => $userFacebook->name,
                        'email' => $userFacebook->email,
                        'external_id' => $userFacebook->id,
                        'external_auth' => 'google',
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;

            // Retornar los datos del usuario junto con el token
            return response()->json([
                'id' => $userExits->id,
                'userName' => $userExits->name,
                'email' => $userExits->email,
                'token' => $token,
            ], 200, [], JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            // Captura cualquier excepción que pueda ocurrir durante el proceso
            Log::info('AuthController->facebookCallback');
            Log::error($e->getMessage());

            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
