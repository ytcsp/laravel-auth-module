<?php

namespace Strichpunkt\LaravelAuthModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'error' => config('auth-module.messages.unauthorized', 'Unauthorized'),
                    'message' => 'User not found'
                ], 401);
            }

            // Add user to request
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => config('auth-module.messages.unauthorized', 'Unauthorized'),
                'message' => 'Token has expired'
            ], 401);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'error' => config('auth-module.messages.unauthorized', 'Unauthorized'),
                'message' => 'Token is invalid'
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'error' => config('auth-module.messages.unauthorized', 'Unauthorized'),
                'message' => 'Token is required'
            ], 401);
        }

        return $next($request);
    }
} 