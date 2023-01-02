<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use \App\Classes\UserData;
use \App\Models\role;
use \App\Models\usersrole;

use App\Http\Controllers\GlobalAuthController;
use Jenssegers\Agent\Agent;
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
	$globalAuth = new GlobalAuthController();
       	$UserData  = $globalAuth->get_data('UserData');//new UserData();
        $user_type = Auth::user()->type;

        if ($role == 'onlysuperadmin') {

            if ($UserData->allow_all()) {
                return $next($request);
            }

            if (!$UserData->is_super_admin()) {
                abort(404);
            }

            return $next($request);

        } else if ($role == 'onlyuser') {

            if ($UserData->allow_all()) {
                return $next($request);
            }

            if ($user_type == 'admin') {
                abort(404);
            }

            return $next($request);
        }

        if ($UserData->is_super_king()) {
            return $next($request);
		}

   
		if ($role == 'super') {
            if ($user_type != 'admin') {
                return abort(404);
            }

            return $next($request);
        }

 	$agent = new Agent();
	if($agent->isMobile()) {
		
		$role_id    = DB::table('mobrole')
				->where('slug', $role)
				->whereNull('deleted_at')
				->first()->id;
		
		$user_roles = DB::table('usersrole')
				->where(['user_id' => Auth::User()->id, 'role_id' => $role_id])
				->whereNull('deleted_at')
				->get();

		
		$module_permission =  $globalAuth->mobile_module($role);

	} else {
        	$role_id    = role::where('slug', $role)->first()->id;
		$user_roles = usersrole::where(
					['user_id' => Auth::User()->id, 'role_id' => $role_id])
			       		->get();

		$module_permission =  $globalAuth->web_module( $role == 'age' ? 'rpt':$role);
	}
		
	if (!$module_permission) {
		\Log::info("You don't have permission to access $role module");
		abort(404);
	}
	

        if ($UserData->is_king()) {
            return $next($request);
        }

           if ($user_roles->count() <= 0 && $user_type != 'admin') {
            return abort(403);
        }

        return $next($request);
    }
}
