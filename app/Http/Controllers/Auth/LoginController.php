<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{

    /**
     * Get the login page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogin()
    {
        return view('auth.login');
    }


    /**
     * Attempt to login a user
     *
     * @return LoginController|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login()
    {
        if (!Auth::attempt(request(['email', 'password']))) {

            return redirect('/login')->withInput(request(['email']))->withErrors([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return redirect('/backstage/concerts/new');
    }


    /**
     * Log the user out
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
