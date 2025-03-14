<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Redirect;
use Auth;
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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        //$this->middleware('guest')->except('logout');
        // if(!Auth::check()) {
        //     return Redirect::action('HomeController@index');
        // }
        if (Auth::check()) {
            return redirect('/home');
        } else {
            return redirect()->route('login');
        }
    }

    public function username(){
        return 'nik';
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login');
    }
}
