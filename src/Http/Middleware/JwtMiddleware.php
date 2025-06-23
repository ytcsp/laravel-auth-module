<?php

namespace Strichpunkt\LaravelAuthModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     * This middleware allows for optional authentication.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if ($user) {
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }
        } catch (JWTException $e) {
            // Token is optional, so we don't return an error
            // The request continues without authentication
        }

        return $next($request);
    }
} 