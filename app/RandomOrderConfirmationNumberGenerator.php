<?php

namespace App;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator, InvitationCodeGenerator {

    private $length = 24;

    public function generate()
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, $this->length)), 0, $this->length);
    }

}