<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ConcertsController extends Controller {

    /**
     * Display the create concerts form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('backstage.concerts.create');
    }


    /**
     * Store new concert details
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $concert = Concert::create([
            'title'                  => request('title'),
            'subtitle'               => request('subtitle'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
            'ticket_price'           => bcmul(request('ticket_price'), 100),
            'venue'                  => request('venue'),
            'venue_address'          => request('venue_address'),
            'city'                   => request('city'),
            'state'                  => request('state'),
            'zip'                    => request('zip'),
            'additional_information' => request('additional_information')

        ])->addTickets(request('ticket_quantity'));

        return redirect()->route('concerts.show', $concert);
    }
}