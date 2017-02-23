<?php

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase {

    use DatabaseMigrations, PaymentGatewayContractTests;

    private $apiKey;
    private $lastCharge;

    protected function setUp()
    {
        parent::setUp();

        $this->apiKey = config('services.stripe.secret');
        $this->lastCharge = $this->lastCharge();
    }


    protected function getPaymentGateway()
    {
        return new StripePaymentGateway($this->apiKey);
    }


    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        try
        {

            // ARRANGE
            // Fake payment gateway
            $paymentGateway = new StripePaymentGateway($this->apiKey);

            // ACT
            // Charge
            $paymentGateway->charge(2500, 'invalid-payment-token');

        } catch (PaymentFailedException $exception)
        {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        // ASSERT
        // If a paymentFailedException is not caught the test fails
        $this->fail('Charging with an invalid payment token did not throw a payment failed exception');
    }


    private function lastCharge()
    {
        return \Stripe\Charge::all(

            ['limit' => 1],
            ['api_key' => $this->apiKey]

        )['data'][0];
    }


    private function validToken()
    {
        return \Stripe\Token::create([
            "card" => [
                "number"    => "4242424242424242",
                "exp_month" => 1,
                "exp_year"  => date('Y') + 1,
                "cvc"       => "123"
            ]
        ], ['api_key' => $this->apiKey])->id;
    }


    private function newCharges()
    {
        return \Stripe\Charge::all(

            [
                'ending_before' => $this->lastCharge ? $this->lastCharge->id : null,
            ],
            ['api_key' => $this->apiKey]

        )['data'];
    }
}