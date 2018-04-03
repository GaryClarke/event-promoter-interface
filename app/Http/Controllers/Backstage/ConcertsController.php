<?php

namespace App\Http\Controllers\Backstage;

use App\Events\ConcertAdded;
use App\NullFile;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ConcertsController extends Controller {

    /**
     * Display a list of the signed-in promoters concerts
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('backstage.concerts.index', [
            'publishedConcerts'   => Auth::user()->concerts->filter->isPublished(),
            'unpublishedConcerts' => Auth::user()->concerts->reject->isPublished(),
        ]);
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
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
            'poster_image'    => ['nullable', 'image', Rule::dimensions()->minWidth(600)->ratio(8.5/11)]
        ]);

        $concert = auth()->user()->concerts()->create([
            'title'                  => request('title'),
            'subtitle'               => request('subtitle'),
            'additional_information' => request('additional_information'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time')
            ])),
            'venue'                  => request('venue'),
            'venue_address'          => request('venue_address'),
            'city'                   => request('city'),
            'state'                  => request('state'),
            'zip'                    => request('zip'),
            'ticket_price'           => request('ticket_price') * 100,
            'ticket_quantity'        => (int) request('ticket_quantity'),
            'poster_image_path'      => request('poster_image', new NullFile)->store('posters', 'public')
        ]);

        ConcertAdded::dispatch($concert);

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


    /**
     * Update concert details
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        $concert = auth()->user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

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
            'ticket_quantity' => ['required', 'integer', 'min:1']
        ]);

        $concert->update([
            'title'                  => request('title'),
            'subtitle'               => request('subtitle'),
            'additional_information' => request('additional_information'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time')
            ])),
            'venue'                  => request('venue'),
            'venue_address'          => request('venue_address'),
            'city'                   => request('city'),
            'state'                  => request('state'),
            'zip'                    => request('zip'),
            'ticket_price'           => request('ticket_price') * 100,
            'ticket_quantity'        => (int) request('ticket_quantity'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}