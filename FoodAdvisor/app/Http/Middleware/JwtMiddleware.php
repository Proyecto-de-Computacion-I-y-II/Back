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

            // Check expiration, if necessary
            $payload = JWTAuth::parseToken()->getPayload();
            $exp = $payload->get('exp');
            $expirationDate = \Carbon\Carbon::createFromTimestamp($exp)->toDateTimeString();
            Log::info('Token Expiration: ' . $expirationDate);

        } catch (Exception $e) {
            // Log the error details
            Log::error('JWT Error: ' . $e->getMessage());
            
            return response()->json(['message' => 'Token no correcto o no proporcionado'], 401);
        }

        return $next($request);
    }
}
