<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $locale = '';
        if ($request->expectsJson()) {
            $locale = $request->header('Accept-Language');
        } else {
            $locale = $request->cookie('Accept-Language');
        }
        // When there is wrong locale set to default spanish language
        if (!in_array($locale, ['es', 'en'])) {
            $locale = 'es';
        }

        app()->setLocale($locale);
        return $next($request);
    }
}
