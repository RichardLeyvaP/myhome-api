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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
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

    public function selectLanguage(Request $request)
    {
        $modelUser = Auth::user();      

        $user = User::find($modelUser->id);
        $locale = $request->input('locale');

        if (!in_array($locale, ['en', 'es', 'pt'])) {
            $locale = 'es';
        }
        

        Log::info('Idioma seleccionado');
        Log::info($locale);


        // Guardar los valores originales antes de hacer cambios
        //$originalValues = $user->getOriginal();

        // Actualizar el idioma del usuario
        $user->language = $locale;

        // Obtener solo los valores que han cambiado
        //$changes = $user->getDirty();

         // Registrar la actividad
            /*activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_values' => Arr::only($originalValues, array_keys($changes)),
                'new_values' => $changes,
            ])
            ->log('Este modelo fue selectLanguage');*/

                App::setLocale($locale);
                session(['locale' => $locale]);

                
        $user->save();

        return response()->json(['message' => __('Idioma seleccionado correctamente.')]);
    }

    public function getUserLanguageChanges(Request $request)
    {
        Log::info(auth()->user()->name.'-'."Busca los cambios de un usuario en la tabla users");
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric'
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
        Log::info($e);
        return response()->json(['error' => 'ServerError'], 500);
    }
    }
}
