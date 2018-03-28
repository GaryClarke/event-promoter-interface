<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublishedConcertsController extends Controller
{

    /**
     * Publish an existing concert
     *
     * Store method name is a little misleading but it is a RESTful implementation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $concert = \Auth::user()->concerts()->findOrFail(request('concert_id'));
        if ($concert->isPublished()) {
            abort(422);
        }
        $concert->publish();
        return redirect()->route('backstage.concerts.index');
    }
}
