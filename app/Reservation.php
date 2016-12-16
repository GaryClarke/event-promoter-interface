<?php

namespace App;


class Reservation {

    private $tickets;

    /**
     * Reservation constructor.
     * @param $tickets
     */
    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * Total cost of ticket reservation
     *
     * @return mixed
     */
    public function totalCost()
    {
        return $this->tickets->sum('price');
    }
}