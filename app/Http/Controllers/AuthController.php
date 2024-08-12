<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Auth', 'Endpoints de Autenticación')]
class AuthController extends Controller
{
    #[Endpoint('login', 'Obtiene un token de acceso mediante sus credenciales', false)]
    #[Response(['data' => ['token_type' => 'Bearer', 'access_token' => '{TOKEN}']])]
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('Las credenciales proporcionadas son incorrectas.')],
            ]);
        }

        $tokenName = $user->name . ' -> Device Type: ' . Browser::deviceType() . ' Device Family: ' . Browser::deviceFamily() . ', OS: ' . Browser::platformName() . (Browser::browserName() ? ', Browser: ' . Browser::browserName() : ', App');

        $accessToken =  $user->createToken($tokenName, ['*'], $request->remember == '1' ? now()->addWeek() : now()->addDay())->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $accessToken
        ]);
    }

    #[Endpoint('profile', 'Muestra los datos del perfil')]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function profile()
    {
        return new UserResource(auth()->user());
    }

    #[Endpoint('logout', 'Cierra la sesión actual')]
    #[Response(['message' => 'Sesión cerrada'])]
    public function logout(Request $request)
    {
        $currentToken = $request->bearerToken();
        $currentTokenSeparate = explode('|', $currentToken);
        auth()->user()->tokens()->where('id', $currentTokenSeparate[0])->delete();

        return response()->json([
            'message' => __('Sesión cerrada')
        ]);
    }
}
