<?php

namespace App\Http\Controllers;

use App\Helpers\AppConstants;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Person;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;
use Spatie\Activitylog\Models\Activity;

/*#[Group('Administrador', 'Endpoints de Administración')]
#[Subgroup('User', 'Endpoints de Usuarios')]*/

class UserController extends Controller
{
    public function __construct() {}


    public function index()
    {
        try {

            Log::info("Entra a buscar los usuarios");
            return response()->json(['users' => User::all()], 200);
        } catch (\Throwable $th) {
            Log::info('UserController->index');
            Log::error($th->getMessage());
            return response()->json(['msg' => "Error interno del sistema"], 500);
        }
    }

    public function register(Request $request)
    {
        Log::info("Registrar usuarios");
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'required|confirmed',
                'email' => 'required|max:50|email|unique:users'
            ]);
            if ($validator->fails()) {
                DB::commit();
                return response()->json([
                    'msg' => $validator->errors()->all()
                ], 400);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
           Person::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'image' => 'people/default.jpg'
            ]);
            DB::commit();
            return response()->json([
                'msg' => "Client registrado correctamente!!!",
                'user' => $user
            ], 201);
        } catch (\Throwable $th) {
            Log::info('UserController->register');
            Log::error($th->getMessage());
            DB::rollback();
            return response()->json(['msg' => $th->getMessage() . 'Error interno del sistema'], 500);
        }
    }

    public function selectLanguage(Request $request)
    {
        try {
            $modelUser = Auth::user();      

            $user = User::find($modelUser->id);
            $locale = $request->input('locale');
    
            if (!in_array($locale, ['en', 'es', 'pt'])) {
                $locale = 'es';
            }
            
    
            Log::info('Idioma seleccionado');
            Log::info($locale);
            $user->language = $locale;
    
                    App::setLocale($locale);
                    session(['locale' => $locale]);    
                    
            $user->save();
    
            return response()->json(['message' => __('Idioma seleccionado correctamente.')], 200);
        } catch (\Throwable $e) {
            Log::info('UserController->selectLanguage');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
        
    }

    public function getUserLanguageChanges(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca los cambios de un usuario en la tabla users");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:users,id'
            ]);
            if ($validator->fails()) {
                return response()->json(['msg' => $validator->errors()->all()], 400);
            }
        $user = User::findOrFail($request->id);

        // Obtener los registros de actividad relacionados con cambios de idioma hechos por el usuario
        // Obtiene el historial de actividades para la tarea
        $activities = Activity::where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json(['activities' => $activities], 200);
        } catch (\Exception $e) {
            Log::info('UserController->show');
            Log::error($e->getMessage());
            return response()->json(['error' => 'ServerError'], 500);
        }
    }
}
