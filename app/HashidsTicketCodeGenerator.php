<?php

namespace App;

use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator {

    private $hashids;

    /**
     * HashidsTicketCodeGenerator constructor.
     */
    public function __construct($salt = 'salt')
    {
        $this->hashids = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }


    /**
     * Generate a ticket code for a given ticket
     *
     * @param $ticket
     * @return string
     */
    public function generateFor($ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}