<?php

namespace App\Http\Controllers\Backstage;

use Zttp\Zttp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StripeConnectController extends Controller {

    public function connect()
    {
        return view('backstage.stripe-connect.connect');
    }


    public function authorizeRedirect()
    {
        $url = vsprintf('%s?%s', [
            'https://connect.stripe.com/oauth/authorize',
            http_build_query([
                'response_type' => 'code',
                'scope'         => 'read_write',
                'client_id'     => config('services.stripe.client_id')
            ]),
        ]);

        return redirect($url);
    }


    public function redirect()
    {
        $accessTokenResponse = Zttp::asFormParams()->post('https://connect.stripe.com/oauth/token', [
            'grant_type'    => 'authorization_code',
            'code'          => request('code'),
            'client_secret' => config('services.stripe.secret'),

        ])->json();

        Auth::user()->update([
            'stripe_account_id'   => $accessTokenResponse['stripe_user_id'],
            'stripe_access_token' => $accessTokenResponse['stripe_access_token'],
        ]);

        info($accessTokenResponse);

        return $this->redirect()->route('backstage.concerts.index');
    }
}
