<?php

namespace App\Http\Middleware;

use Closure;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if(empty($user) || !$user->hasRole('user')){
            return response()->json(['message' => 'Unauthenticated. User role required'], 401);
        }
        return $next($request);
    }
}
