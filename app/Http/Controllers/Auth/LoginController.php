<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use Jenssegers\Agent\Agent;
use Log;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;



    public function login(Request $request){
		Log::debug('LoginController@login');

		// Session::reflash('redirect');
		$request->session()->flush();

        $this->validateLogin($request);
        $userData = User::where('email',$request->email)->first();

        if ($userData) {
            if ($userData->status == 'pending') {
                session()->flash('message', 'Your new account is pending approval. Please refer to administration for further information.');
                return back();
            }
            if ($userData->status == 'inactive') {
                session()->flash('message', 'Your account is Inactive. Please refer to administration for further information.');
                return back();
            }
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }


	protected function sendLoginResponse(Request $request)
    {
		Log::debug('sendLoginResponse()');

        $request->session()->regenerate();
        $date_time = date('Y-m-d h:m:s');
        $request->session()->put('login_time',$date_time);

        $this->clearLoginAttempts($request);

        if ($this->guard()->user()->type == 'admin') {
            $this->redirectTo = '/show-superadminlanding-view';

			Log::debug('ADMIN');

        } else {
            $agent = new Agent();
            if ( $agent->isMobile() ) {
                $this->redirectTo = '/mob_landing';

				Log::debug('MOBILE');

            } else {
                $this->redirectTo = '/landing';

				Log::debug('WEB');
            }
        }

        return $this->authenticated($request, $this->guard()->user())
                ? : redirect()->intended($this->redirectTo);
    }
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/landing';
    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
