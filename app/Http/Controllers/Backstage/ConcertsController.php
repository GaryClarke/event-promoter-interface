<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;

class ConcertsController extends Controller {

    /**
     * Display a list of the signed-in promoters concerts
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

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
        $this->validate(request(), [
            'title'           => ['required'],
            'date'            => ['required', 'date'],
            'time'            => ['required', 'date_format:g:ia'],
            'venue'           => ['required'],
            'venue_address'   => ['required'],
            'city'            => ['required'],
            'state'           => ['required'],
            'zip'             => ['required'],
            'ticket_price'    => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'numeric', 'min:1']
        ]);

        $concert = auth()->user()->concerts()->create([
            'title'                  => request('title'),
            'subtitle'               => request('subtitle'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [request('date'), request('time')])),
            'ticket_price'           => bcmul(request('ticket_price'), 100),
            'venue'                  => request('venue'),
            'venue_address'          => request('venue_address'),
            'city'                   => request('city'),
            'state'                  => request('state'),
            'zip'                    => request('zip'),
            'additional_information' => request('additional_information'),

        ])->addTickets(request('ticket_quantity'));

        $concert->publish();

        return redirect()->route('concerts.show', $concert);
    }


    /**
     * Edit an existing concert
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

        return view('backstage.concerts.edit', [
           'concert' => $concert
        ]);
    }
}