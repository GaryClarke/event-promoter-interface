<?php

namespace App;


class Reservation {

    private $tickets;

    private $email;

    /**
     * Reservation constructor.
     * @param $tickets
     */
    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }


    /**************************************** Getters ****************************************/


    /**
     * Get the reservations tickets
     *
     * @return mixed
     */
    function tickets()
    {
        return $this->tickets;
    }


    function email()
    {
        return $this->email;
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


    /**
     * Complete a reservation
     *
     * @param $paymentGateway
     * @param $paymentToken
     * @return Order
     */
    public function complete($paymentGateway, $paymentToken)
    {
        $charge = $paymentGateway->charge($this->totalCost(), $paymentToken);

        return $order = Order::forTickets($this->tickets(), $this->email(), $charge);
    }


    /**
     * Cancelling a reservation releases the tickets
     */
    public function cancel()
    {

        foreach ($this->tickets as $ticket)
        {
            $ticket->release();
        }
    }
}