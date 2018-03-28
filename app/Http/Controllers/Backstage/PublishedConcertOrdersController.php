<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;

class PublishedConcertOrdersController extends Controller {

    public function index($concertId)
    {
        $concert = \Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published-concert-orders.index', [
           'concert' => $concert,
            'orders' => $concert->orders()->latest()->take(10)->get(),
        ]);
    }
}