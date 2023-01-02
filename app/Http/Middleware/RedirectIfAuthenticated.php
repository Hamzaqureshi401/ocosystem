<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
		if (Auth::guard($guard)->check()) {
			return redirect()->intended('/landing');
		}

        if($request->ajax()) {
            if(empty(Auth::user())){
                return response()->json([
                    'SESSION_STATUS' => 'NOT_LOGGED_IN'
                ],440);
            } else{
                return $next($request);
            }

		} else{
			return $next($request);
		}

        return $next($request);
    }
}
