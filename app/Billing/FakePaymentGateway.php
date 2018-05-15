<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway {

    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;
    const TEST_CARD_NUMBER = 4242424242424242;


    /**
     * FakePaymentGateway constructor.
     */
    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }


    /**
     * Payment token
     *
     * @param int|string $cardNumber
     * @return string
     */
    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token = 'fake-tok_' . str_random(24);

        $this->tokens[$token] = $cardNumber;

        return $token;
    }


    /**
     * Charge a specified amount
     *
     * @param $amount
     * @param $token
     * @return Charge
     */
    public function charge($amount, $token, $destinationAccountId)
    {
        if ($this->beforeFirstChargeCallback !== null)
        {
            $callback = $this->beforeFirstChargeCallback;

            $this->beforeFirstChargeCallback = null;

            $callback($this);
        }

        if (!$this->tokens->has($token))
        {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount'              => $amount,
            'card_last_four'      => substr($this->tokens[$token], - 4),
            'destination_account' => $destinationAccountId
        ]);
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
        return $this->charges->map->amount()->sum();
    }


    /**
     * Sum total charges for a destination account
     *
     * @param $accountId
     * @return mixed
     */
    public function totalChargesFor($accountId)
    {
        return $this->charges->filter(function ($charge) use ($accountId) {

            return $charge->destinationAccount() === $accountId;

        })->map->amount()->sum();
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