<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Log;

class JwtMiddleware extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Log the token from the request header
            $token = JWTAuth::getToken();
            Log::info('Received Token: ' . $token);  // Log the token
            // Parse the token and authenticate the user
            $user = JWTAuth::parseToken()->authenticate();

            // Verificar si el usuario tiene rol de administrador
            if (!$user || $user->rol !== 'admin') {
                Log::warning('Acceso denegado: Usuario no es administrador', ['user_id' => $user ? $user->id : null]);
                return response()->json(['message' => 'No tienes permisos de administrador'], 403);
            }

        } catch (Exception $e) {
            // Log the error details
            Log::error('JWT Error: ' . $e->getMessage());

            if (strpos($e->getMessage(), 'expired') !== false){
                return response()->json(['message' => 'El token ha expirado'], 401);
            }
            
            return response()->json(['message' => 'Token no correcto o no proporcionado'], 401);
        }

        return $next($request);
    }
}
