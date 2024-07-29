<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AddCustomClaims
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user is authenticated
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            // Set custom claims
            $customClaims = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ];

            $token = JWTAuth::fromUser($admin);
            $token = JWTAuth::setToken($token)->setCustomClaims($customClaims)->refresh();

            // Optionally, you can attach the new token to the request or response
            // For this example, we're assuming you'll return the token in the response directly
            $response = $next($request);
            $response->headers->set('Authorization', 'Bearer ' . $token);

            return $response;
        }

        return $next($request);
    }
}
