<?php

namespace App\Billing;

class Charge {

    public $data;

    /**
     * Charge constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
     * Get the last four of the charged card
     *
     * @return mixed
     */
    public function cardLastFour()
    {
        return $this->data['card_last_four'];
    }


    /**
     * Get the charged amount
     *
     * @return mixed
     */
    public function amount()
    {
        return $this->data['amount'];
    }
}