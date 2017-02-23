<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway {

    private $charges;

    private $beforeFirstChargeCallback;


    /**
     * FakePaymentGateway constructor.
     */
    public function __construct()
    {
        $this->charges = collect();
    }


    /**
     * Payment token
     *
     * @return string
     */
    public function getValidTestToken()
    {
        return 'valid-token';
    }


    /**
     * Charge a specified amount
     *
     * @param $amount
     * @param $token
     */
    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null) {

            $callback = $this->beforeFirstChargeCallback;

            $this->beforeFirstChargeCallback = null;

            $callback($this);
        }

        if ($token !== $this->getValidTestToken())
        {

            throw new PaymentFailedException;
        }

        $this->charges[] = $amount;
    }


    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();
        $callback($this);
        return $this->charges->slice($chargesFrom)->reverse()->values();
    }


    /**
     * Sum total charges
     *
     * @return mixed
     */
    public function totalCharges()
    {
        return $this->charges->sum();
    }


    /**
     * A callback to run before the first charge is made
     *
     * @param $callback
     */
    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}