<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAppLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Determinar el idioma predeterminado
        $defaultLocale = 'es'; // Idioma por defecto si no se especifica uno válido
        // Verificar si el usuario está autenticado
        if (Auth::user()) {
            $locale = Auth::user()->language;
            Log::info('User is authenticated. Locale from DB: ' . $locale);
        } else {
            // Si el usuario no está autenticado, verificar el idioma desde el encabezado o la cookie
            $locale = $request->expectsJson() 
                ? $request->header('Accept-Language') 
                : $request->cookie('Accept-Language');
            Log::info('User is not authenticated. Locale from header or cookie: ' . $locale);
        }

        // Validar y establecer el idioma
        if (!in_array($locale, ['es', 'en', 'pt'])) {
            $locale = $defaultLocale;
        }

        // Configurar el idioma de la aplicación
        App::setLocale($locale);

        // Log para depurar el idioma establecido
        Log::info('Locale set to: ' . $locale);

        return $next($request);
        /*$locale = '';
        if ($request->expectsJson()) {
            $locale = $request->header('Accept-Language');
        } else {
            $locale = $request->cookie('Accept-Language');
        }
        // When there is wrong locale set to default spanish language
        if (!in_array($locale, ['es', 'en', 'pt'])) {
            $locale = 'es';
        }

        app()->setLocale($locale);
        return $next($request);*/
    }
}
