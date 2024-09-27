<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Person;
use App\Models\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        Log::info("Entra a loguearse al Sistema desde la administración");
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
            Log::info($th->getMessage());
            return response()->json(['msg' => 'ServerError'], 500);
        }
    }

    public function loginApk(Request $request)
    {
        Log::info("Entra a loguearse al Sistema desde el apk");
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
            Log::info($th->getMessage());
            return response()->json(['msg' => 'ServerError'], 500);
        }
    }

    public function logout(Request $request)
    {
        Log::info(auth()->user()->name . '-' . "Cierra Session");
        try {
            auth()->user()->tokens()->delete();
            return response()->json(["msg" => "CloseSessionOk"], 200);
        } catch (\Throwable $th) {
            Log::info('AuthController->logout');
            Log::info($th->getMessage());
            return response()->json(['msg' => 'ServerError'], 500);
        }
    }

    public function googleCallback()
    {
        Log::info('Logueo por cuenta de google');
        DB::beginTransaction();
        try {
            // Intentar obtener el usuario de Google
            $userGoogle = Socialite::driver('google')->stateless()->user();

            // Verificar si $userGoogle es null
            if (!$userGoogle) {
                DB::commit();
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
                    Person::create([
                        'user_id' => $userExits->id,
                        'name' => $userGoogle->name,
                        'email' => $userGoogle->email,
                        'image' => 'people/default.jpg'
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;
            DB::commit();
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
            Log::error($e->getMessage());
            DB::rollback();
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function facebookCallback()
    {
        Log::info('Logueo por cuenta de facebook');
        DB::beginTransaction();
        try {
            // Intentar obtener el usuario de Google
            $userFacebook = Socialite::driver('facebook')->stateless()->user();

            // Verificar si $userFacebook es null
            if (!$userFacebook) {
                DB::commit();
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
                    Person::create([
                        'user_id' => $userExits->id,
                        'name' => $userFacebook->name,
                        'email' => $userFacebook->email,
                        'image' => 'people/default.jpg'
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;
            DB::commit();
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
            DB::rollback();
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function googleCallbackApk(Request $request)
    {
        Log::info('Logueo por cuenta de Google desde APK');
        DB::beginTransaction();
        try {
            // Obtener el token de Google desde la solicitud
            $idToken = $request->input('id_token');

            // Verificar si se proporcionó el token
            if (!$idToken) {
                DB::commit();
                return response()->json(['error' => 'TokenNoProporcionado'], 400);
            }

            // Obtener el usuario de Google a partir del token proporcionado
            $userGoogle = Socialite::driver('google')->stateless()->userFromToken($idToken);

            // Verificar si $userGoogle es null
            if (!$userGoogle) {
                DB::commit();
                return response()->json(['msg' => 'GoogleNotFound'], 400);
            }

            // Verificar si el usuario ya existe en la base de datos
            $userExits = User::where('external_id', $userGoogle->id)
                ->where('external_auth', 'google')
                ->first();

            if ($userExits) {
                // Iniciar sesión con el usuario existente
                Auth::login($userExits);
            } else {
                // Verificar si el correo electrónico ya está registrado
                $emailExits = User::where('email', $userGoogle->email)->first();
                if ($emailExits) {
                    // Actualizar el registro existente con la información de Google
                    $emailExits->update([
                        'external_id' => $userGoogle->id,
                        'external_auth' => 'google',
                    ]);
                    $userExits = $emailExits;
                    Auth::login($userExits);
                } else {
                    // Crear un nuevo usuario en la base de datos
                    $userExits = User::create([
                        'name' => $userGoogle->name,
                        'email' => $userGoogle->email,
                        'external_id' => $userGoogle->id,
                        'external_auth' => 'google',
                    ]);
                    Person::create([
                        'user_id' => $userExits->id,
                        'name' => $userGoogle->name,
                        'email' => $userGoogle->email,
                        'image' => 'people/default.jpg'
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;
            DB::commit();
            // Retornar los datos del usuario junto con el token
            return response()->json([
                'id' => $userExits->id,
                'userName' => $userExits->name,
                'email' => $userExits->email,
                'token' => $token,
            ], 200, [], JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            // Captura cualquier excepción que pueda ocurrir durante el proceso
            Log::info('AuthController->googleCallbackApk');
            Log::error($e->getMessage());
            DB::rollback();
            return response()->json(['error' => 'ServerError'], 500);
        }
    }

    public function facebookCallbackApk(Request $request)
    {
        Log::info('Logueo por cuenta de Facebook desde APK');

        try {
            // Obtener el access_token de Facebook desde la solicitud
            $accessToken = $request->input('access_token');

            // Verificar si se proporcionó el token
            if (!$accessToken) {
                DB::commit();
                return response()->json(['error' => 'TokenNoProporcionado'], 400);
            }

            // Obtener el usuario de Facebook a partir del token proporcionado
            $userFacebook = Socialite::driver('facebook')->stateless()->userFromToken($accessToken);

            // Verificar si $userFacebook es null
            if (!$userFacebook) {
                DB::commit();
                return response()->json(['msg' => 'FacebookNotFound'], 400);
            }

            // Verificar si el usuario ya existe en la base de datos
            $userExits = User::where('external_id', $userFacebook->id)
                ->where('external_auth', 'facebook')
                ->first();

            if ($userExits) {
                // Iniciar sesión con el usuario existente
                Auth::login($userExits);
            } else {
                // Verificar si el correo electrónico ya está registrado
                $emailExits = User::where('email', $userFacebook->email)->first();
                if ($emailExits) {
                    // Actualizar el registro existente con la información de Facebook
                    $emailExits->update([
                        'external_id' => $userFacebook->id,
                        'external_auth' => 'facebook',
                    ]);
                    $userExits = $emailExits;
                    Auth::login($userExits);
                } else {
                    // Crear un nuevo usuario en la base de datos
                    $userExits = User::create([
                        'name' => $userFacebook->name,
                        'email' => $userFacebook->email,
                        'external_id' => $userFacebook->id,
                        'external_auth' => 'facebook', // Cambiado de 'google' a 'facebook'
                    ]);

                    Person::create([
                        'user_id' => $userExits->id,
                        'name' => $userFacebook->name,
                        'email' => $userFacebook->email,
                        'image' => 'people/default.jpg'
                    ]);
                    Auth::login($userExits);
                }
            }

            // Generar el token usando Sanctum
            $token = $userExits->createToken('auth_token')->plainTextToken;
            DB::commit();
            // Retornar los datos del usuario junto con el token
            return response()->json([
                'id' => $userExits->id,
                'userName' => $userExits->name,
                'email' => $userExits->email,
                'token' => $token,
            ], 200, [], JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            // Captura cualquier excepción que pueda ocurrir durante el proceso
            Log::info('AuthController->facebookCallbackApk');
            Log::error($e->getMessage());
            DB::rollback();
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
