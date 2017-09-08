<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{

    /**
     * Attempt to login a user
     *
     * @return LoginController|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login()
    {
        if (!Auth::attempt(request(['email', 'password']))) {

            return redirect('/login')->withErrors([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return redirect('/backstage/concerts');
    }
}
