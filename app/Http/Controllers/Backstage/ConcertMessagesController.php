<?php

namespace App\Http\Controllers\Backstage;

use App\Jobs\SendAttendeeMessage;
use App\Http\Controllers\Controller;

class ConcertMessagesController extends Controller {

    /**
     * New messages page
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($id)
    {
        $concert = \Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }


    public function store($id)
    {
        $concert = \Auth::user()->concerts()->findOrFail($id);

        $this->validate(request(), [
            'subject' => ['required'],
            'message' => ['required']
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)
            ->with('flash', 'Your message has been sent');
    }
}