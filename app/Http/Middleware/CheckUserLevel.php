<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$levels): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (is_null($user->user_level_id) || $user->user_level_id == 0) {
                Auth::logout();
                
                // Only redirect if we are not already on the login page
                if (!$request->routeIs('login')) {
                    return redirect()->route('login')->with('error', 'inactive');
                }
            }

            if (!empty($levels) && !in_array($user->user_level_id, $levels)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
