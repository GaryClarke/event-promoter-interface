<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Invitation;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RegisterController extends Controller {

    public function register()
    {
        $invitation = Invitation::findByCode(request('invitation_code'));

        $user = User::create([
            'email'    => request('email'),
            'password' => bcrypt(request('password'))
        ]);

        $invitation->update([
           'user_id' => $user->id
        ]);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}
